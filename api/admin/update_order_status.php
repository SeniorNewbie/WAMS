<?php
// api/admin/update_order_status.php - API do aktualizacji statusu zamówienia
require_once '../../config/database.php';
require_once '../../config/security.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

initSession();
requireAdmin();

$conn = getDBConnection();

$orderId = intval($_POST['order_id'] ?? 0);
$paymentStatus = sanitizeInput($_POST['payment_status'] ?? '');
$orderStatus = sanitizeInput($_POST['order_status'] ?? '');

// Walidacja statusów
$validPaymentStatuses = ['Nieopłacone', 'Opłacone', 'Częściowo opłacone'];
$validOrderStatuses = [
    'Zamówienie czeka na potwierdzenie',
    'Zamówienie przyjęte',
    'Zamówienie czeka na realizację',
    'Zamówienie jest w trakcie realizacji',
    'Zamówienie jest gotowe do odbioru'
];

if (!in_array($paymentStatus, $validPaymentStatuses) || !in_array($orderStatus, $validOrderStatuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nieprawidłowy status'
    ]);
    exit();
}

// Pobierz poprzednie statusy
$stmt = $conn->prepare("SELECT payment_status, order_status, user_id FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$previousOrder = $result->fetch_assoc();

if (!$previousOrder) {
    echo json_encode([
        'success' => false,
        'message' => 'Zamówienie nie istnieje'
    ]);
    exit();
}

// Aktualizuj statusy
$stmt = $conn->prepare("
    UPDATE orders 
    SET payment_status = ?, order_status = ?, updated_at = NOW() 
    WHERE id = ?
");
$stmt->bind_param("ssi", $paymentStatus, $orderStatus, $orderId);

if ($stmt->execute()) {
    // Utwórz powiadomienia dla klienta jeśli zmienił się status
    $notifications = [];
    
    if ($previousOrder['payment_status'] != $paymentStatus) {
        $notifications[] = [
            'type' => 'order_payment_update',
            'title' => 'Zmiana statusu płatności',
            'message' => "Status płatności zamówienia został zmieniony na: $paymentStatus"
        ];
    }
    
    if ($previousOrder['order_status'] != $orderStatus) {
        $notifications[] = [
            'type' => 'order_status_update',
            'title' => 'Zmiana statusu zamówienia',
            'message' => "Status realizacji zamówienia został zmieniony na: $orderStatus"
        ];
    }
    
    // Wyślij powiadomienia
    foreach ($notifications as $notification) {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, type, title, message, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param(
            "isss",
            $previousOrder['user_id'],
            $notification['type'],
            $notification['title'],
            $notification['message']
        );
        $stmt->execute();
    }
    
    // Zapisz aktywność
    logActivity($conn, $_SESSION['user_id'], 'update_order_status', "order_$orderId");
    
    echo json_encode([
        'success' => true,
        'message' => 'Status został zaktualizowany'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Błąd podczas aktualizacji statusu'
    ]);
}
?>