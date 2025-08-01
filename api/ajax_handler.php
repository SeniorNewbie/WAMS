<?php
// api/ajax_handler.php - Główny handler dla żądań AJAX
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

header('Content-Type: application/json');

initSession();

// Sprawdź czy użytkownik jest zalogowany (dla większości akcji)
$publicActions = ['check_email', 'validate_nip'];
$action = $_REQUEST['action'] ?? '';

if (!in_array($action, $publicActions)) {
    requireLogin();
}

$conn = getDBConnection();

try {
    switch ($action) {
        // Akcje publiczne
        case 'check_email':
            checkEmailAvailability($conn);
            break;
            
        case 'validate_nip':
            validateNIPNumber($conn);
            break;
            
        // Akcje użytkownika
        case 'update_profile':
            updateProfile($conn);
            break;
            
        case 'change_password':
            changePassword($conn);
            break;
            
        case 'search_products':
            searchProducts($conn);
            break;
            
        case 'get_notifications_count':
            getNotificationsCount($conn);
            break;
            
        case 'mark_notification_read':
            markNotificationRead($conn);
            break;
            
        // Akcje administracyjne
        case 'get_dashboard_stats':
            requireAdmin();
            getDashboardStats($conn);
            break;
            
        case 'export_data':
            requireAdmin();
            exportData($conn);
            break;
            
        case 'system_health_check':
            requireAdmin();
            systemHealthCheck($conn);
            break;
            
        default:
            throw new Exception('Nieznana akcja');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    
    // Loguj błąd
    if (isLoggedIn()) {
        debugLog($conn, 'ERROR', $e->getMessage(), __FILE__, $e->getLine());
    }
}

// Funkcje obsługujące poszczególne akcje

function checkEmailAvailability($conn) {
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (!validateEmail($email)) {
        echo json_encode([
            'success' => false,
            'available' => false,
            'message' => 'Nieprawidłowy format email'
        ]);
        return;
    }
    
    $exists = recordExists($conn, 'users', 'email', $email);
    
    echo json_encode([
        'success' => true,
        'available' => !$exists,
        'message' => $exists ? 'Email jest już zajęty' : 'Email jest dostępny'
    ]);
}

function validateNIPNumber($conn) {
    $nip = sanitizeInput($_POST['nip'] ?? '');
    
    $isValid = validateNIP($nip);
    
    echo json_encode([
        'success' => true,
        'valid' => $isValid,
        'message' => $isValid ? 'NIP jest prawidłowy' : 'Nieprawidłowy numer NIP'
    ]);
}

function updateProfile($conn) {
    $userId = $_SESSION['user_id'];
    $data = $_POST;
    
    // Walidacja
    $errors = validateFormData($data, [
        'first_name' => ['required' => true, 'min_length' => 2],
        'last_name' => ['required' => true, 'min_length' => 2],
        'street' => ['required' => true],
        'postal_code' => ['required' => true],
        'city' => ['required' => true]
    ]);
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        return;
    }
    
    // Aktualizuj dane
    $stmt = $conn->prepare("
        UPDATE company_data 
        SET first_name = ?, last_name = ?, street = ?, building_number = ?, 
            postal_code = ?, city = ?
        WHERE user_id = ?
    ");
    
    $stmt->bind_param(
        "ssssssi",
        $data['first_name'],
        $data['last_name'],
        $data['street'],
        $data['building_number'],
        $data['postal_code'],
        $data['city'],
        $userId
    );
    
    if ($stmt->execute()) {
        logActivity($conn, $userId, 'update_profile', 'success');
        
        echo json_encode([
            'success' => true,
            'message' => 'Profil został zaktualizowany'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Błąd podczas aktualizacji profilu'
        ]);
    }
}

function changePassword($conn) {
    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Sprawdź obecne hasło
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!verifyPassword($currentPassword, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Obecne hasło jest nieprawidłowe'
        ]);
        return;
    }
    
    // Walidacja nowego hasła
    if ($newPassword !== $confirmPassword) {
        echo json_encode([
            'success' => false,
            'message' => 'Hasła nie są identyczne'
        ]);
        return;
    }
    
    if (!validatePasswordStrength($newPassword)) {
        echo json_encode([
            'success' => false,
            'message' => 'Hasło musi zawierać min. 8 znaków, wielką literę, małą literę, cyfrę i znak specjalny'
        ]);
        return;
    }
    
    // Zmień hasło
    $hashedPassword = hashPassword($newPassword);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    
    if ($stmt->execute()) {
        logActivity($conn, $userId, 'change_password', 'success');
        
        echo json_encode([
            'success' => true,
            'message' => 'Hasło zostało zmienione'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Błąd podczas zmiany hasła'
        ]);
    }
}

function searchProducts($conn) {
    $query = sanitizeInput($_GET['q'] ?? '');
    
    if (strlen($query) < 2) {
        echo json_encode([
            'success' => true,
            'results' => []
        ]);
        return;
    }
    
    $sql = "
        SELECT DISTINCT product_name as name, COUNT(*) as count
        FROM order_permissions
        WHERE product_name LIKE ? AND user_id = ?
        GROUP BY product_name
        LIMIT 10
    ";
    
    $searchQuery = "%$query%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $searchQuery, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'results' => $products
    ]);
}

function getNotificationsCount($conn) {
    $userId = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread 
        FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['unread'];
    
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
}

function markNotificationRead($conn) {
    $userId = $_SESSION['user_id'];
    $notificationId = intval($_POST['notification_id'] ?? 0);
    
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $notificationId, $userId);
    
    echo json_encode([
        'success' => $stmt->execute()
    ]);
}

function getDashboardStats($conn) {
    $stats = [];
    
    // Statystyki zamówień
    $result = $conn->query("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total_net) as total_revenue,
            COUNT(CASE WHEN payment_status = 'Opłacone' THEN 1 END) as paid_orders,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_orders
        FROM orders
    ");
    $stats['orders'] = $result->fetch_assoc();
    
    // Statystyki klientów
    $result = $conn->query("
        SELECT 
            COUNT(*) as total_clients,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_clients,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_clients
        FROM users
        WHERE user_type IN ('client', 'subaccount')
    ");
    $stats['clients'] = $result->fetch_assoc();
    
    // Statystyki reklamacji
    $result = $conn->query("
        SELECT 
            COUNT(*) as total_complaints,
            COUNT(CASE WHEN status = 'Złożona reklamacja' THEN 1 END) as pending_complaints
        FROM complaints
    ");
    $stats['complaints'] = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

function exportData($conn) {
    $type = sanitizeInput($_GET['type'] ?? '');
    $format = sanitizeInput($_GET['format'] ?? 'csv');
    
    // Tutaj można dodać logikę eksportu danych
    // Na przykład generowanie CSV lub Excel
    
    echo json_encode([
        'success' => true,
        'message' => 'Funkcja eksportu w przygotowaniu'
    ]);
}

function systemHealthCheck($conn) {
    $health = [
        'database' => true,
        'files' => true,
        'permissions' => true,
        'disk_space' => true
    ];
    
    // Sprawdź połączenie z bazą
    try {
        $conn->query("SELECT 1");
    } catch (Exception $e) {
        $health['database'] = false;
    }
    
    // Sprawdź katalog uploadu
    $uploadPath = '../assets/uploads/';
    if (!is_writable($uploadPath)) {
        $health['files'] = false;
    }
    
    // Sprawdź przestrzeń dyskową
    $freeSpace = disk_free_space('/');
    $totalSpace = disk_total_space('/');
    if ($freeSpace / $totalSpace < 0.1) { // Mniej niż 10% wolnego miejsca
        $health['disk_space'] = false;
    }
    
    $allHealthy = !in_array(false, $health);
    
    echo json_encode([
        'success' => true,
        'healthy' => $allHealthy,
        'checks' => $health,
        'disk_usage' => [
            'free' => formatBytes($freeSpace),
            'total' => formatBytes($totalSpace),
            'percentage' => round(($totalSpace - $freeSpace) / $totalSpace * 100, 2)
        ]
    ]);
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>