<?php
// api/catalog_files.php - API do pobierania plików z katalogów
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

header('Content-Type: application/json');

initSession();
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$catalogId = intval($_GET['catalog_id'] ?? 0);

// Sprawdź uprawnienia do katalogu
if (!isAdmin()) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as has_access 
        FROM catalog_permissions 
        WHERE user_id = ? AND catalog_id = ? AND can_view = 1
    ");
    $stmt->bind_param("ii", $userId, $catalogId);
    $stmt->execute();
    $result = $stmt->get_result();
    $hasAccess = $result->fetch_assoc()['has_access'] > 0;
    
    if (!$hasAccess) {
        echo json_encode(['error' => 'Brak uprawnień do tego katalogu']);
        exit();
    }
}

// Pobierz pliki katalogu
$stmt = $conn->prepare("
    SELECT * FROM catalog_files 
    WHERE catalog_id = ? 
    ORDER BY file_type, file_name
");
$stmt->bind_param("i", $catalogId);
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}

// Zapisz aktywność
logActivity($conn, $userId, 'view_catalog_files', "catalog_$catalogId");

echo json_encode($files);
?>