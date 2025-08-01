<?php
// api/complaint_submit.php - API do składania reklamacji
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

header('Content-Type: application/json');

initSession();
requireClient();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Pobierz dane
$orderId = intval($_POST['order_id'] ?? 0);
$reason = sanitizeInput($_POST['reason'] ?? '');
$productElement = sanitizeInput($_POST['product_element'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');

// Walidacja
if (empty($orderId) || empty($reason) || empty($productElement) || empty($description)) {
    echo json_encode([
        'success' => false,
        'message' => 'Wszystkie pola są wymagane'
    ]);
    exit();
}

// Sprawdź czy zamówienie należy do użytkownika
$stmt = $conn->prepare("
    SELECT o.*, u.parent_id 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.payment_status = 'Opłacone'
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo json_encode([
        'success' => false,
        'message' => 'Zamówienie nie istnieje lub nie jest opłacone'
    ]);
    exit();
}

// Sprawdź uprawnienia
if ($order['user_id'] != $userId) {
    // Sprawdź czy to subkonto
    $stmt = $conn->prepare("SELECT parent_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || $user['parent_id'] != $order['user_id']) {
        echo json_encode([
            'success' => false,
            'message' => 'Brak uprawnień do tego zamówienia'
        ]);
        exit();
    }
}

// Sprawdź czy nie ma już reklamacji dla tego zamówienia
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM complaints 
    WHERE order_id = ? AND status != 'Reklamacja zakończona'
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$existingComplaints = $result->fetch_assoc()['count'];

if ($existingComplaints > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Dla tego zamówienia istnieje już aktywna reklamacja'
    ]);
    exit();
}

// Rozpocznij transakcję
beginTransaction($conn);

try {
    // Generuj numer reklamacji
    $complaintNumber = generateUniqueNumber($conn, 'complaints', 'complaint_number', 'REK/');
    
    // Dodaj reklamację
    $stmt = $conn->prepare("
        INSERT INTO complaints (
            complaint_number, user_id, order_id, reason, 
            product_element, description, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'Złożona reklamacja', NOW())
    ");
    
    $stmt->bind_param(
        "siisss",
        $complaintNumber,
        $userId,
        $orderId,
        $reason,
        $productElement,
        $description
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Błąd podczas zapisywania reklamacji");
    }
    
    $complaintId = $conn->insert_id;
    
    // Utwórz powiadomienie dla administratorów
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, type, title, message, created_at)
        SELECT id, 'new_complaint', 'Nowa reklamacja', ?, NOW()
        FROM users WHERE user_type = 'admin'
    ");
    $message = "Złożono nową reklamację nr $complaintNumber do zamówienia {$order['order_number']}";
    $stmt->bind_param("s", $message);
    $stmt->execute();
    
    // Utwórz powiadomienie dla klienta
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, type, title, message, created_at)
        VALUES (?, 'new_complaint', 'Reklamacja przyjęta', ?, NOW())
    ");
    $clientMessage = "Twoja reklamacja nr $complaintNumber została przyjęta do rozpatrzenia";
    $stmt->bind_param("is", $userId, $clientMessage);
    $stmt->execute();
    
    // Zapisz aktywność
    logActivity($conn, $userId, 'submit_complaint', $complaintNumber);
    
    // Zatwierdź transakcję
    commitTransaction($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Reklamacja została zgłoszona pomyślnie',
        'complaint_number' => $complaintNumber,
        'complaint_id' => $complaintId
    ]);
    
} catch (Exception $e) {
    // Wycofaj transakcję
    rollbackTransaction($conn);
    
    // Loguj błąd
    debugLog($conn, 'ERROR', $e->getMessage(), __FILE__, __LINE__);
    
    echo json_encode([
        'success' => false,
        'message' => 'Wystąpił błąd podczas zgłaszania reklamacji'
    ]);
}
?>