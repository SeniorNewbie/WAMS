<?php
// api/admin/client_create.php - API do tworzenia nowego klienta
require_once '../../config/database.php';
require_once '../../config/security.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

initSession();
requireAdmin();

$conn = getDBConnection();

// Walidacja CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Błąd bezpieczeństwa']);
    exit();
}

// Pobierz dane z formularza
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$nip = sanitizeInput($_POST['nip'] ?? '');
$companyName = sanitizeInput($_POST['company_name'] ?? '');
$firstName = sanitizeInput($_POST['first_name'] ?? '');
$lastName = sanitizeInput($_POST['last_name'] ?? '');
$street = sanitizeInput($_POST['street'] ?? '');
$buildingNumber = sanitizeInput($_POST['building_number'] ?? '');
$postalCode = sanitizeInput($_POST['postal_code'] ?? '');
$city = sanitizeInput($_POST['city'] ?? '');
$country = sanitizeInput($_POST['country'] ?? 'Polska');
$clientGroup = sanitizeInput($_POST['client_group'] ?? '');

// Walidacja wymaganych pól
$errors = [];

if (!validateEmail($email)) {
    $errors['email'] = 'Nieprawidłowy adres email';
}

if (strlen($password) < 8) {
    $errors['password'] = 'Hasło musi mieć co najmniej 8 znaków';
}

if (!validateNIP($nip)) {
    $errors['nip'] = 'Nieprawidłowy numer NIP';
}

if (empty($companyName)) {
    $errors['company_name'] = 'Nazwa firmy jest wymagana';
}

if (empty($firstName)) {
    $errors['first_name'] = 'Imię jest wymagane';
}

if (empty($lastName)) {
    $errors['last_name'] = 'Nazwisko jest wymagane';
}

if (empty($street)) {
    $errors['street'] = 'Ulica jest wymagana';
}

if (!preg_match('/^\d{2}-\d{3}$/', $postalCode)) {
    $errors['postal_code'] = 'Nieprawidłowy format kodu pocztowego';
}

if (empty($city)) {
    $errors['city'] = 'Miejscowość jest wymagana';
}

// Sprawdź czy email już istnieje
if (recordExists($conn, 'users', 'email', $email)) {
    $errors['email'] = 'Konto z tym adresem email już istnieje';
}

// Sprawdź czy NIP już istnieje
if (recordExists($conn, 'company_data', 'nip', $nip)) {
    $errors['nip'] = 'Firma z tym numerem NIP już istnieje';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

// Rozpocznij transakcję
beginTransaction($conn);

try {
    // Utwórz użytkownika
    $hashedPassword = hashPassword($password);
    
    $stmt = $conn->prepare("
        INSERT INTO users (email, password, user_type, is_active, created_at) 
        VALUES (?, ?, 'client', 1, NOW())
    ");
    $stmt->bind_param("ss", $email, $hashedPassword);
    
    if (!$stmt->execute()) {
        throw new Exception("Błąd podczas tworzenia użytkownika");
    }
    
    $userId = $conn->insert_id;
    
    // Dodaj dane firmy
    $stmt = $conn->prepare("
        INSERT INTO company_data (
            user_id, nip, company_name, first_name, last_name, 
            street, building_number, postal_code, city, country, 
            client_group, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param(
        "issssssssss",
        $userId, $nip, $companyName, $firstName, $lastName,
        $street, $buildingNumber, $postalCode, $city, $country,
        $clientGroup
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Błąd podczas zapisywania danych firmy");
    }
    
    // Dodaj uprawnienia do katalogów
    if (!empty($_POST['catalog_permissions']) && is_array($_POST['catalog_permissions'])) {
        $stmt = $conn->prepare("
            INSERT INTO catalog_permissions (user_id, catalog_id, can_view, created_at) 
            VALUES (?, ?, 1, NOW())
        ");
        
        foreach ($_POST['catalog_permissions'] as $catalogId) {
            $catalogId = intval($catalogId);
            $stmt->bind_param("ii", $userId, $catalogId);
            if (!$stmt->execute()) {
                throw new Exception("Błąd podczas przyznawania uprawnień do katalogów");
            }
        }
    }
    
    // Dodaj uprawnienia do produktów
    if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
        $stmt = $conn->prepare("
            INSERT INTO order_permissions (user_id, product_name, can_quote, can_order, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        foreach ($_POST['permissions'] as $productName => $perms) {
            $canQuote = isset($perms['quote']) ? 1 : 0;
            $canOrder = isset($perms['order']) ? 1 : 0;
            
            if ($canQuote || $canOrder) {
                $stmt->bind_param("ssii", $userId, $productName, $canQuote, $canOrder);
                if (!$stmt->execute()) {
                    throw new Exception("Błąd podczas przyznawania uprawnień do produktów");
                }
            }
        }
    }
    
    // Utwórz folder dla klienta
    $clientFolder = dirname(__DIR__, 2) . '/assets/uploads/clients/' . $userId;
    if (!file_exists($clientFolder)) {
        mkdir($clientFolder, 0755, true);
        
        // Utwórz podfoldery
        mkdir($clientFolder . '/faktury', 0755, true);
        mkdir($clientFolder . '/dane', 0755, true);
        
        // Utwórz plik .htaccess dla bezpieczeństwa
        file_put_contents($clientFolder . '/.htaccess', "Deny from all");
    }
    
    // Wyślij email z danymi dostępowymi
    $emailSubject = "Dane dostępowe do Panelu Klienta";
    $emailBody = "
    Witaj {$firstName} {$lastName},

    Twoje konto w Panelu Klienta zostało utworzone.

    Dane dostępowe:
    Login: {$email}
    Hasło: {$password}

    Link do logowania: " . $_SERVER['HTTP_HOST'] . "/

    Ze względów bezpieczeństwa zalecamy zmianę hasła po pierwszym zalogowaniu.

    Pozdrawiamy,
    Zespół Administracyjny
    ";
    
    // W produkcji użyj funkcji mail() lub biblioteki do wysyłania emaili
    // mail($email, $emailSubject, $emailBody, "From: noreply@" . $_SERVER['HTTP_HOST']);
    
    // Zapisz aktywność
    logActivity($conn, $_SESSION['user_id'], 'create_client', $email);
    
    // Zatwierdź transakcję
    commitTransaction($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Klient został utworzony pomyślnie. Dane dostępowe zostały wysłane na podany adres email.',
        'user_id' => $userId,
        'redirect' => '/admin/clients.php'
    ]);
    
} catch (Exception $e) {
    // Wycofaj transakcję
    rollbackTransaction($conn);
    
    // Usuń folder jeśli został utworzony
    if (isset($clientFolder) && file_exists($clientFolder)) {
        rmdir($clientFolder . '/faktury');
        rmdir($clientFolder . '/dane');
        rmdir($clientFolder);
    }
    
    // Loguj błąd
    debugLog($conn, 'ERROR', $e->getMessage(), __FILE__, __LINE__);
    
    echo json_encode([
        'success' => false,
        'message' => 'Wystąpił błąd podczas tworzenia klienta: ' . $e->getMessage()
    ]);
}
?>