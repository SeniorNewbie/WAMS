<?php
// api/admin/upload_invoice.php - API do przesyłania faktur
require_once '../../config/database.php';
require_once '../../config/security.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

initSession();
requireAdmin();

$conn = getDBConnection();

$orderId = intval($_POST['order_id'] ?? 0);

// Sprawdź czy przesłano plik
if (!isset($_FILES['invoice']) || $_FILES['invoice']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'Nie przesłano pliku lub wystąpił błąd'
    ]);
    exit();
}

// Walidacja pliku
$file = $_FILES['invoice'];
$validation = validateFileUpload($file, ['pdf'], 10485760); // 10MB max

if (!$validation['success']) {
    echo json_encode($validation);
    exit();
}

// Pobierz informacje o zamówieniu
$stmt = $conn->prepare("
    SELECT o.*, u.id as client_id 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo json_encode([
        'success' => false,
        'message' => 'Zamówienie nie istnieje'
    ]);
    exit();
}

// Utwórz folder dla klienta jeśli nie istnieje
$clientFolder = '../assets/uploads/clients/' . $order['client_id'] . '/faktury';
if (!file_exists($clientFolder)) {
    mkdir($clientFolder, 0755, true);
}

// Generuj nazwę pliku
$fileName = 'FV_' . $order['order