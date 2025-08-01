<?php
// api/download.php - API do pobierania plików
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$type = sanitizeInput($_GET['type'] ?? '');
$fileId = intval($_GET['file_id'] ?? 0);
$orderId = intval($_GET['order_id'] ?? 0);

try {
    switch ($type) {
        case 'catalog':
            downloadCatalogFile($conn, $userId, $fileId);
            break;
            
        case 'client':
            downloadClientFile($conn, $userId, $fileId);
            break;
            
        case 'invoice':
            downloadInvoice($conn, $userId, $orderId);
            break;
            
        default:
            throw new Exception('Nieprawidłowy typ pliku');
    }
} catch (Exception $e) {
    http_response_code(404);
    die('Plik nie został znaleziony');
}

function downloadCatalogFile($conn, $userId, $fileId) {
    // Pobierz informacje o pliku
    $stmt = $conn->prepare("
        SELECT cf.*, c.id as catalog_id
        FROM catalog_files cf
        JOIN catalogs c ON cf.catalog_id = c.id
        WHERE cf.id = ?
    ");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    
    if (!$file) {
        throw new Exception('Plik nie istnieje');
    }
    
    // Sprawdź uprawnienia
    if (!isAdmin()) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as has_access 
            FROM catalog_permissions 
            WHERE user_id = ? AND catalog_id = ? AND can_view = 1
        ");
        $stmt->bind_param("ii", $userId, $file['catalog_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $hasAccess = $result->fetch_assoc()['has_access'] > 0;
        
        if (!$hasAccess) {
            throw new Exception('Brak uprawnień');
        }
    }
    
    // Wyślij plik
    sendFile($file['file_path'], $file['file_name']);
    
    // Zapisz aktywność
    logActivity($conn, $userId, 'download_catalog_file', $file['file_name']);
}

function downloadClientFile($conn, $userId, $fileId) {
    // Pobierz informacje o pliku
    $stmt = $conn->prepare("
        SELECT * FROM client_files 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $fileId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    
    if (!$file) {
        // Sprawdź czy to admin
        if (isAdmin()) {
            $stmt = $conn->prepare("SELECT * FROM client_files WHERE id = ?");
            $stmt->bind_param("i", $fileId);
            $stmt->execute();
            $result = $stmt->get_result();
            $file = $result->fetch_assoc();
        }
        
        if (!$file) {
            throw new Exception('Plik nie istnieje lub brak uprawnień');
        }
    }
    
    // Wyślij plik
    sendFile($file['file_path'], $file['file_name']);
    
    // Zapisz aktywność
    logActivity($conn, $userId, 'download_client_file', $file['file_name']);
}

function downloadInvoice($conn, $userId, $orderId) {
    // Pobierz informacje o zamówieniu
    $stmt = $conn->prepare("
        SELECT o.*, cd.company_name 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN company_data cd ON u.id = cd.user_id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order) {
        throw new Exception('Zamówienie nie istnieje');
    }
    
    // Sprawdź uprawnienia
    if (!isAdmin() && $order['user_id'] != $userId) {
        // Sprawdź czy to subkonto
        $stmt = $conn->prepare("SELECT parent_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user['parent_id'] != $order['user_id']) {
            throw new Exception('Brak uprawnień');
        }
    }
    
    if (empty($order['invoice_path'])) {
        throw new Exception('Brak faktury dla tego zamówienia');
    }
    
    // Wyślij plik
    sendFile($order['invoice_path'], 'Faktura_' . $order['order_number'] . '.pdf');
    
    // Zapisz aktywność
    logActivity($conn, $userId, 'download_invoice', $order['order_number']);
}

function sendFile($filePath, $fileName) {
    // Pełna ścieżka do pliku
    $fullPath = '../' . $filePath;
    
    if (!file_exists($fullPath)) {
        throw new Exception('Plik nie istnieje na serwerze');
    }
    
    // Określ typ MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fullPath);
    finfo_close($finfo);
    
    // Ustaw nagłówki
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($fullPath));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: no-cache');
    
    // Wyślij plik
    readfile($fullPath);
    exit();
}
?>