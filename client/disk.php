<?php
// client/disk.php - Dysk klienta
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireClient();
checkSessionTimeout();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Pobierz pliki użytkownika
$stmt = $conn->prepare("
    SELECT cf.*, u.email as uploaded_by_email
    FROM client_files cf
    JOIN users u ON cf.uploaded_by = u.id
    WHERE cf.user_id = ?
    ORDER BY cf.folder_name, cf.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Grupuj pliki według folderów
$filesByFolder = [];
foreach ($files as $file) {
    $filesByFolder[$file['folder_name']][] = $file;
}

// Domyślne foldery
$defaultFolders = ['faktury', 'dane'];
foreach ($defaultFolders as $folder) {
    if (!isset($filesByFolder[$folder])) {
        $filesByFolder[$folder] = [];
    }
}

// Zapisz aktywność
logActivity($conn, $userId, 'view_disk', 'disk');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dysk klienta - Panel Klienta</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .disk-stats {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        
        .folder-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        
        .folder-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .folder-header {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .folder-header i {
            font-size: 32px;
            color: var(--primary-color);
        }
        
        .folder-title {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
            text-transform: capitalize;
        }
        
        .folder-count {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .folder-content {
            padding: 16px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .file-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .file-item {
            padding: 12px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .file-item:hover {
            background: #f5f5f5;
        }
        
        .file-item:last-child {
            border-bottom: none;
        }
        
        .file-icon {
            font-size: 24px;
            color: var(--text-secondary);
        }
        
        .file-details {
            flex: 1;
        }
        
        .file-name {
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .file-info {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .file-actions {
            display: flex;
            gap: 8px;
        }
        
        .empty-folder {
            text-align: center;
            padding: 48px 16px;
            color: var(--text-secondary);
        }
        
        .empty-folder i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }
        
        .disk-info {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .disk-info i {
            font-size: 24px;
            color: #1976d2;
        }
    </style>
</head>
<body class="mdc-typography">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Dysk klienta</h1>
            </div>
            
            <!-- Informacja o dysku -->
            <div class="disk-info">
                <i class="material-icons">info</i>
                <div>
                    <strong>Informacja:</strong> Pliki są dodawane przez administratora. 
                    Jeśli potrzebujesz dodać pliki, skontaktuj się z działem obsługi.
                </div>
            </div>
            
            <!-- Statystyki -->
            <div class="disk-stats">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($files); ?></div>
                        <div class="stat-label">Wszystkie pliki</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($filesByFolder); ?></div>
                        <div class="stat-label">Foldery</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">
                            <?php
                            $totalSize = array_sum(array_column($files, 'file_size'));
                            echo formatFileSize($totalSize);
                            ?>
                        </div>
                        <div class="stat-label">Całkowity rozmiar</div>
                    </div>
                </div>
            </div>
            
            <!-- Foldery -->
            <div class="folder-grid">
                <?php foreach ($filesByFolder as $folderName => $folderFiles): ?>
                <div class="folder-card">
                    <div class="folder-header">
                        <i class="material-icons">
                            <?php echo $folderName === 'faktury' ? 'receipt' : 'folder'; ?>
                        </i>
                        <div>
                            <h3 class="folder-title"><?php echo escape($folderName); ?></h3>
                            <div class="folder-count"><?php echo count($folderFiles); ?> plików</div>
                        </div>
                    </div>
                    <div class="folder-content">
                        <?php if (empty($folderFiles)): ?>
                        <div class="empty-folder">
                            <i class="material-icons">folder_open</i>
                            <p>Folder jest pusty</p>
                        </div>
                        <?php else: ?>
                        <ul class="file-list">
                            <?php foreach ($folderFiles as $file): ?>
                            <li class="file-item">
                                <i class="material-icons file-icon"><?php echo getFileIcon($file['file_name']); ?></i>
                                <div class="file-details">
                                    <div class="file-name"><?php echo escape($file['file_name']); ?></div>
                                    <div class="file-info">
                                        <?php echo formatFileSize($file['file_size']); ?> • 
                                        <?php echo date('d.m.Y H:i', strtotime($file['created_at'])); ?> • 
                                        Dodał: <?php echo escape($file['uploaded_by_email']); ?>
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <button class="mdc-icon-button material-icons" 
                                            title="Pobierz"
                                            onclick="downloadFile(<?php echo $file['id']; ?>)">
                                        download
                                    </button>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function downloadFile(fileId) {
            window.location.href = '../api/download.php?type=client&file_id=' + fileId;
        }
    </script>
</body>
</html>

<?php
// Funkcje pomocnicze
function formatFileSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    elseif ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
    elseif ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
    else return round($bytes / 1073741824, 2) . ' GB';
}

function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => 'picture_as_pdf',
        'doc' => 'description',
        'docx' => 'description',
        'xls' => 'table_chart',
        'xlsx' => 'table_chart',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image',
        'zip' => 'folder_zip',
        'rar' => 'folder_zip',
        'txt' => 'text_snippet',
        'mp4' => 'videocam',
        'avi' => 'videocam',
        'mov' => 'videocam'
    ];
    return $icons[$ext] ?? 'insert_drive_file';
}
?>