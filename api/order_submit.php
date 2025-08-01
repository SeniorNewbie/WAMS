<?php
// api/order_submit.php - API do składania zamówień
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

header('Content-Type: application/json');

initSession();
requireClient();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Pobierz dane JSON
$input = json_decode(file_get_contents('php://input'), true);

// Walidacja CSRF
if (!verifyCSRFToken($input['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Błąd bezpieczeństwa']);
    exit();
}

// Walidacja danych
if (empty($input['items']) || !is_array($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Brak pozycji w zamówieniu']);
    exit();
}

if (count($input['items']) > 20) {
    echo json_encode(['success' => false, 'message' => 'Maksymalna liczba pozycji to 20']);
    exit();
}

// Sprawdź uprawnienia
$stmt = $conn->prepare("
    SELECT COUNT(*) as can_order 
    FROM order_permissions 
    WHERE user_id = ? AND can_order = 1
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$canOrder = $result->fetch_assoc()['can_order'] > 0;

if (!$canOrder) {
    echo json_encode(['success' => false, 'message' => 'Brak uprawnień do składania zamówień']);
    exit();
}

// Rozpocznij transakcję
beginTransaction($conn);

try {
    // Generuj numer zamówienia
    $orderNumber = generateUniqueNumber($conn, 'orders', 'order_number', 'ZAM/');
    
    // Oblicz sumę zamówienia
    $totalNet = 0;
    foreach ($input['items'] as $item) {
        $totalNet += floatval($item['price'] ?? 0);
    }
    
    // Utwórz zamówienie
    $stmt = $conn->prepare("
        INSERT INTO orders (order_number, user_id, total_net, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("sid", $orderNumber, $userId, $totalNet);
    
    if (!$stmt->execute()) {
        throw new Exception("Błąd podczas tworzenia zamówienia");
    }
    
    $orderId = $conn->insert_id;
    
    // Dodaj pozycje zamówienia
    $stmt = $conn->prepare("
        INSERT INTO order_items (
            order_id, position_number, product, width, height, quantity,
            profile_system, profile_color, net_color, window_rabbet,
            hook_size, riveting, force_hook, force_hook_count,
            horizontal_bar, bar_height, supplier_note, total_width,
            total_height, customer_label, self_assembly, corner_type,
            powder_color, corner_color_ral, price_net
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $positionNumber = 1;
    foreach ($input['items'] as $item) {
        // Walidacja wymaganych pól
        if (empty($item['product']) || empty($item['width']) || empty($item['height']) || 
            empty($item['quantity']) || empty($item['profile_system']) || 
            empty($item['profile_color']) || empty($item['net_color']) || 
            empty($item['window_rabbet'])) {
            throw new Exception("Brakuje wymaganych pól w pozycji $positionNumber");
        }
        
        $stmt->bind_param(
            "iisiiiisssissiisisssisssd",
            $orderId,
            $positionNumber,
            $item['product'],
            $item['width'],
            $item['height'],
            $item['quantity'],
            $item['profile_system'],
            $item['profile_color'],
            $item['net_color'],
            $item['window_rabbet'],
            $item['hook_size'],
            $item['riveting'],
            $item['force_hook'],
            $item['force_hook_count'],
            $item['horizontal_bar'],
            $item['bar_height'],
            $item['supplier_note'],
            $item['total_width'],
            $item['total_height'],
            $item['customer_label'],
            $item['self_assembly'],
            $item['corner_type'],
            $item['powder_color'],
            $item['corner_color_ral'],
            $item['price']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Błąd podczas dodawania pozycji $positionNumber");
        }
        
        $positionNumber++;
    }
    
    // Dodaj komentarz do zamówienia jeśli istnieje
    if (!empty($input['order_comment'])) {
        $stmt = $conn->prepare("
            UPDATE orders SET comment = ? WHERE id = ?
        ");
        $stmt->bind_param("si", $input['order_comment'], $orderId);
        $stmt->execute();
    }
    
    // Utwórz powiadomienie dla administratorów
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, type, title, message, created_at)
        SELECT id, 'new_order', 'Nowe zamówienie', ?, NOW()
        FROM users WHERE user_type = 'admin'
    ");
    $message = "Złożono nowe zamówienie nr $orderNumber";
    $stmt->bind_param("s", $message);
    $stmt->execute();
    
    // Utwórz powiadomienie dla klienta
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, type, title, message, created_at)
        VALUES (?, 'new_order', 'Zamówienie złożone', ?, NOW())
    ");
    $clientMessage = "Twoje zamówienie nr $orderNumber zostało przyjęte do realizacji";
    $stmt->bind_param("is", $userId, $clientMessage);
    $stmt->execute();
    
    // Zapisz aktywność
    logActivity($conn, $userId, 'create_order', $orderNumber);
    
    // Zatwierdź transakcję
    commitTransaction($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Zamówienie zostało złożone pomyślnie',
        'order_number' => $orderNumber,
        'order_id' => $orderId
    ]);
    
} catch (Exception $e) {
    // Wycofaj transakcję
    rollbackTransaction($conn);
    
    // Loguj błąd
    debugLog($conn, 'ERROR', $e->getMessage(), __FILE__, __LINE__);
    
    echo json_encode([
        'success' => false,
        'message' => 'Wystąpił błąd podczas składania zamówienia: ' . $e->getMessage()
    ]);
}
?>