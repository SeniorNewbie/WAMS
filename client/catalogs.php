<?php
// client/catalogs.php - Moduł katalogów dla klienta
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireClient();
checkSessionTimeout();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Pobierz katalogi do których użytkownik ma dostęp
$stmt = $conn->prepare("
    SELECT DISTINCT c.*, c2.name as parent_name
    FROM catalogs c
    LEFT JOIN catalogs c2 ON c.parent_id = c2.id
    LEFT JOIN catalog_permissions cp ON c.id = cp.catalog_id
    WHERE c.is_active = 1 AND cp.user_id = ? AND cp.can_view = 1
    ORDER BY c.parent_id, c.name
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$catalogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Organizuj katalogi w strukturę drzewa
$catalogTree = [];
$childCatalogs = [];

foreach ($catalogs as $catalog) {
    if ($catalog['parent_id'] === null) {
        $catalogTree[$catalog['id']] = $catalog;
    } else {
        $childCatalogs[$catalog['parent_id']][] = $catalog;
    }
}

// Pobierz reklamy dla katalogów
$catalogIds = array_column($catalogs, 'id');
$ads = [];
if (!empty($catalogIds)) {
    $placeholders = str_repeat('?,', count($catalogIds) - 1) . '?';
    $stmt = $conn->prepare("
        SELECT a.*, ac.catalog_id
        FROM advertisements a
        JOIN ad_catalogs ac ON a.id = ac.ad_id
        WHERE a.is_active = 1 
        AND a.start_date <= CURDATE() 
        AND a.end_date >= CURDATE()
        AND ac.catalog_id IN ($placeholders)
    ");
    $types = str_repeat('i', count($catalogIds));
    $stmt->bind_param($types, ...$catalogIds);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ads[$row['catalog_id']][] = $row;
    }
}

// Zapisz aktywność
logActivity($conn, $userId, 'view_catalogs', 'catalogs');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Katalogi - Panel Klienta</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .catalog-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .catalog-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .catalog-icon {
            height: 120px;
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .catalog-icon i {
            font-size: 48px;
            color: var(--primary-color);
        }
        
        .catalog-content {
            padding: 16px;
        }
        
        .catalog-title {
            font-size: 18px;
            font-weight: 500;
            margin: 0 0 8px 0;
        }
        
        .catalog-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .subcatalog-list {
            margin-top: 16px;
        }
        
        .subcatalog-item {
            padding: 12px 16px;
            border-left: 3px solid var(--primary-color);
            margin-bottom: 8px;
            background: #f5f5f5;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .subcatalog-item:hover {
            background: #e3f2fd;
            transform: translateX(4px);
        }
        
        .catalog-files {
            margin-top: 24px;
        }
        
        .file-section {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .file-section-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .file-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            transition: all 0.2s ease;
        }
        
        .file-item:hover {
            background: #f5f5f5;
            border-color: var(--primary-color);
        }
        
        .file-item i {
            font-size: 48px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .file-name {
            font-size: 14px;
            word-break: break-word;
            margin-bottom: 8px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .ad-banner {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .ad-banner img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="mdc-typography">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Katalogi produktów</h1>
            </div>
            
            <!-- Breadcrumb -->
            <div class="breadcrumb" id="breadcrumb">
                <a href="catalogs.php">Katalogi</a>
            </div>
            
            <!-- Reklamy -->
            <div id="ads-container"></div>
            
            <!-- Lista katalogów -->
            <div id="catalog-list">
                <?php if (empty($catalogTree)): ?>
                <div class="empty-state">
                    <i class="material-icons">folder_off</i>
                    <h3>Brak dostępnych katalogów</h3>
                    <p>Nie masz uprawnień do żadnych katalogów.</p>
                </div>
                <?php else: ?>
                <div class="catalog-grid">
                    <?php foreach ($catalogTree as $parentId => $parent): ?>
                    <div class="catalog-card" data-catalog-id="<?php echo $parent['id']; ?>">
                        <div class="catalog-icon">
                            <i class="material-icons">folder</i>
                        </div>
                        <div class="catalog-content">
                            <h3 class="catalog-title"><?php echo escape($parent['name']); ?></h3>
                            <?php if (isset($childCatalogs[$parentId])): ?>
                            <p class="catalog-subtitle">
                                <?php echo count($childCatalogs[$parentId]); ?> podkatalogów
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Zawartość katalogu (ukryta domyślnie) -->
            <div id="catalog-content" style="display: none;">
                <div id="subcatalogs-list"></div>
                <div id="catalog-files"></div>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/main.js"></script>
    <script>
        let currentCatalog = null;
        let catalogData = <?php echo json_encode($catalogs); ?>;
        let childCatalogs = <?php echo json_encode($childCatalogs); ?>;
        let adsData = <?php echo json_encode($ads); ?>;
        
        // Obsługa kliknięcia w katalog
        $(document).on('click', '.catalog-card', function() {
            const catalogId = $(this).data('catalog-id');
            showCatalog(catalogId);
        });
        
        // Obsługa kliknięcia w podkatalog
        $(document).on('click', '.subcatalog-item', function(e) {
            e.stopPropagation();
            const catalogId = $(this).data('catalog-id');
            showCatalog(catalogId);
        });
        
        // Wyświetlanie katalogu
        function showCatalog(catalogId) {
            currentCatalog = catalogId;
            const catalog = catalogData.find(c => c.id == catalogId);
            
            if (!catalog) return;
            
            // Aktualizuj breadcrumb
            updateBreadcrumb(catalog);
            
            // Pokaż reklamy jeśli są
            showAds(catalogId);
            
            // Ukryj listę główną
            $('#catalog-list').hide();
            $('#catalog-content').show();
            
            // Pokaż podkatalogi jeśli są
            const subcatalogs = childCatalogs[catalogId] || [];
            if (subcatalogs.length > 0) {
                let html = '<div class="subcatalog-list"><h2>Podkatalogi</h2><div class="catalog-grid">';
                subcatalogs.forEach(sub => {
                    html += `
                        <div class="subcatalog-item" data-catalog-id="${sub.id}">
                            <h4>${sub.name}</h4>
                        </div>
                    `;
                });
                html += '</div></div>';
                $('#subcatalogs-list').html(html);
            } else {
                $('#subcatalogs-list').empty();
            }
            
            // Załaduj pliki katalogu
            loadCatalogFiles(catalogId);
        }
        
        // Aktualizacja breadcrumb
        function updateBreadcrumb(catalog) {
            let breadcrumb = '<a href="catalogs.php">Katalogi</a>';
            
            if (catalog.parent_id) {
                const parent = catalogData.find(c => c.id == catalog.parent_id);
                if (parent) {
                    breadcrumb += ` <i class="material-icons">chevron_right</i> <a href="#" onclick="showCatalog(${parent.id}); return false;">${parent.name}</a>`;
                }
            }
            
            breadcrumb += ` <i class="material-icons">chevron_right</i> ${catalog.name}`;
            $('#breadcrumb').html(breadcrumb);
        }
        
        // Pokazywanie reklam
        function showAds(catalogId) {
            const ads = adsData[catalogId] || [];
            if (ads.length > 0) {
                let html = '';
                ads.forEach(ad => {
                    html += `
                        <div class="ad-banner">
                            <img src="${ad.image_path}" alt="${ad.name}">
                        </div>
                    `;
                });
                $('#ads-container').html(html);
            } else {
                $('#ads-container').empty();
            }
        }
        
        // Ładowanie plików katalogu
        function loadCatalogFiles(catalogId) {
            $.ajax({
                url: '../api/catalog_files.php',
                method: 'GET',
                data: { catalog_id: catalogId },
                success: function(response) {
                    displayCatalogFiles(response);
                },
                error: function() {
                    showNotification('error', 'Błąd ładowania plików');
                }
            });
        }
        
        // Wyświetlanie plików
        function displayCatalogFiles(files) {
            if (!files || files.length === 0) {
                $('#catalog-files').html('<p class="text-center">Brak plików w tym katalogu</p>');
                return;
            }
            
            // Grupuj pliki według typu
            const filesByType = {
                'instrukcje': [],
                'jak_zainstalowac': [],
                'zdjecia': [],
                'wideo': []
            };
            
            files.forEach(file => {
                if (filesByType[file.file_type]) {
                    filesByType[file.file_type].push(file);
                }
            });
            
            let html = '<div class="catalog-files">';
            
            // Instrukcje
            if (filesByType.instrukcje.length > 0) {
                html += renderFileSection('Instrukcje', 'description', filesByType.instrukcje);
            }
            
            // Jak zainstalować
            if (filesByType.jak_zainstalowac.length > 0) {
                html += renderFileSection('Jak zainstalować', 'build', filesByType.jak_zainstalowac);
            }
            
            // Zdjęcia
            if (filesByType.zdjecia.length > 0) {
                html += renderFileSection('Zdjęcia', 'image', filesByType.zdjecia);
            }
            
            // Wideo
            if (filesByType.wideo.length > 0) {
                html += renderFileSection('Wideo', 'videocam', filesByType.wideo);
            }
            
            html += '</div>';
            $('#catalog-files').html(html);
        }
        
        // Renderowanie sekcji plików
        function renderFileSection(title, icon, files) {
            let html = `
                <div class="file-section">
                    <h3 class="file-section-title">
                        <i class="material-icons">${icon}</i>
                        ${title}
                    </h3>
                    <div class="file-grid">
            `;
            
            files.forEach(file => {
                const fileIcon = getFileIcon(file.file_name);
                html += `
                    <div class="file-item" onclick="downloadFile(${file.id})">
                        <i class="material-icons">${fileIcon}</i>
                        <div class="file-name">${file.file_name}</div>
                        <button class="mdc-button mdc-button--outlined">
                            <i class="material-icons mdc-button__icon">download</i>
                            Pobierz
                        </button>
                    </div>
                `;
            });
            
            html += '</div></div>';
            return html;
        }
        
        // Pobieranie pliku
        function downloadFile(fileId) {
            window.location.href = '../api/download.php?type=catalog&file_id=' + fileId;
        }
        
        // Ikona na podstawie rozszerzenia
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                'pdf': 'picture_as_pdf',
                'doc': 'description',
                'docx': 'description',
                'jpg': 'image',
                'jpeg': 'image',
                'png': 'image',
                'mp4': 'videocam',
                'avi': 'videocam',
                'mov': 'videocam'
            };
            return icons[ext] || 'insert_drive_file';
        }
    </script>
</body>
</html>