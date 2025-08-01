<?php
// admin/ads.php - Zarządzanie reklamami
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireAdmin();
checkSessionTimeout();

$conn = getDBConnection();

// Pobierz reklamy
$ads = fetchAll($conn, "
    SELECT a.*,
           (SELECT COUNT(*) FROM ad_catalogs WHERE ad_id = a.id) as catalogs_count,
           (SELECT COUNT(*) FROM ad_clients WHERE ad_id = a.id) as clients_count,
           (SELECT COUNT(*) FROM ad_client_groups WHERE ad_id = a.id) as groups_count
    FROM advertisements a
    ORDER BY a.created_at DESC
");

// Pobierz katalogi
$catalogs = fetchAll($conn, "
    SELECT c1.*, c2.name as parent_name 
    FROM catalogs c1
    LEFT JOIN catalogs c2 ON c1.parent_id = c2.id
    WHERE c1.is_active = 1
    ORDER BY c1.parent_id, c1.name
");

// Pobierz klientów
$clients = fetchAll($conn, "
    SELECT u.id, cd.company_name, cd.first_name, cd.last_name, cd.client_group
    FROM users u
    JOIN company_data cd ON u.id = cd.user_id
    WHERE u.user_type IN ('client', 'subaccount')
    ORDER BY cd.company_name
");

// Grupy klientów
$clientGroups = ['Klient Moskitiery', 'Klient Rolety', 'Klient Roletki', 'Klient Żaluzje', 'Klient Duży'];

// Zapisz aktywność
logActivity($conn, $_SESSION['user_id'], 'view_ads_admin', 'admin_ads');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Zarządzanie reklamami - Panel Administracyjny</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .ads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }
        
        .ad-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .ad-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .ad-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f5f5f5;
        }
        
        .ad-content {
            padding: 16px;
        }
        
        .ad-title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .ad-info {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 12px;
        }
        
        .ad-dates {
            display: flex;
            gap: 16px;
            margin-bottom: 12px;
            font-size: 13px;
        }
        
        .ad-date {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .ad-stats {
            display: flex;
            gap: 16px;
            padding-top: 12px;
            border-top: 1px solid #f5f5f5;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .ad-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }
        
        .status-indicator {
            position: absolute;
            top: 16px;
            right: 16px;
            background: white;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .status-indicator.active {
            background: #4caf50;
            color: white;
        }
        
        .status-indicator.inactive {
            background: #f44336;
            color: white;
        }
        
        .status-indicator.scheduled {
            background: #ff9800;
            color: white;
        }
        
        .status-indicator.expired {
            background: #9e9e9e;
            color: white;
        }
        
        /* Dialog form */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .form-grid-full {
            grid-column: 1 / -1;
        }
        
        .image-preview {
            margin-top: 16px;
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
        }
        
        .selection-lists {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 16px;
        }
        
        .selection-list {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .selection-list h4 {
            margin: 0 0 16px 0;
            font-size: 16px;
        }
        
        .catalog-tree {
            margin-left: 16px;
        }
        
        .no-ads {
            text-align: center;
            padding: 64px;
            color: var(--text-secondary);
        }
        
        .no-ads i {
            font-size: 64px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body class="mdc-typography admin">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Zarządzanie reklamami</h1>
                <button class="mdc-button mdc-button--raised" onclick="showAddAdDialog()">
                    <i class="material-icons mdc-button__icon">add</i>
                    <span class="mdc-button__label">Dodaj reklamę</span>
                </button>
            </div>
            
            <?php if (empty($ads)): ?>
            <div class="no-ads">
                <i class="material-icons">campaign</i>
                <h3>Brak reklam</h3>
                <p>Nie dodano jeszcze żadnych reklam.</p>
                <button class="mdc-button mdc-button--raised" onclick="showAddAdDialog()">
                    <span class="mdc-button__label">Dodaj pierwszą reklamę</span>
                </button>
            </div>
            <?php else: ?>
            <div class="ads-grid">
                <?php foreach ($ads as $ad): ?>
                <?php
                $now = time();
                $startDate = strtotime($ad['start_date']);
                $endDate = strtotime($ad['end_date']);
                
                if (!$ad['is_active']) {
                    $status = 'inactive';
                    $statusText = 'Nieaktywna';
                } elseif ($now < $startDate) {
                    $status = 'scheduled';
                    $statusText = 'Zaplanowana';
                } elseif ($now > $endDate) {
                    $status = 'expired';
                    $statusText = 'Wygasła';
                } else {
                    $status = 'active';
                    $statusText = 'Aktywna';
                }
                ?>
                <div class="ad-card">
                    <div style="position: relative;">
                        <img src="<?php echo escape($ad['image_path']); ?>" 
                             alt="<?php echo escape($ad['name']); ?>" 
                             class="ad-image"
                             onerror="this.src='../assets/img/placeholder.jpg'">
                        <span class="status-indicator <?php echo $status; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </div>
                    <div class="ad-content">
                        <h3 class="ad-title"><?php echo escape($ad['name']); ?></h3>
                        <div class="ad-info">Nr: <?php echo escape($ad['ad_number']); ?></div>
                        
                        <div class="ad-dates">
                            <div class="ad-date">
                                <i class="material-icons" style="font-size: 16px;">event</i>
                                <?php echo date('d.m.Y', strtotime($ad['end_date'])); ?>
                            </div>
                        </div>
                        
                        <div class="ad-stats">
                            <span>
                                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">folder</i>
                                <?php echo $ad['catalogs_count']; ?> katalogów
                            </span>
                            <span>
                                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">people</i>
                                <?php echo $ad['clients_count']; ?> klientów
                            </span>
                            <span>
                                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">group</i>
                                <?php echo $ad['groups_count']; ?> grup
                            </span>
                        </div>
                        
                        <div class="ad-actions">
                            <button class="action-button secondary" onclick="editAd(<?php echo $ad['id']; ?>)">
                                <i class="material-icons">edit</i>
                                Edytuj
                            </button>
                            <button class="action-button secondary" 
                                    onclick="toggleAdStatus(<?php echo $ad['id']; ?>, <?php echo $ad['is_active'] ? '0' : '1'; ?>)">
                                <i class="material-icons"><?php echo $ad['is_active'] ? 'visibility_off' : 'visibility'; ?></i>
                                <?php echo $ad['is_active'] ? 'Dezaktywuj' : 'Aktywuj'; ?>
                            </button>
                            <button class="action-button danger" onclick="deleteAd(<?php echo $ad['id']; ?>)">
                                <i class="material-icons">delete</i>
                                Usuń
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Dialog dodawania/edycji reklamy -->
    <div class="mdc-dialog" id="adDialog">
        <div class="mdc-dialog__container">
            <div class="mdc-dialog__surface" style="width: 800px; max-width: 90vw;">
                <h2 class="mdc-dialog__title" id="dialogTitle">Dodaj reklamę</h2>
                <div class="mdc-dialog__content">
                    <form id="adForm">
                        <input type="hidden" name="ad_id" id="ad_id">
                        
                        <div class="form-grid">
                            <!-- Nazwa reklamy -->
                            <div class="form-grid-full">
                                <div class="mdc-text-field mdc-text-field--outlined" style="width: 100%;">
                                    <input type="text" id="name" name="name" class="mdc-text-field__input" required>
                                    <div class="mdc-notched-outline">
                                        <div class="mdc-notched-outline__leading"></div>
                                        <div class="mdc-notched-outline__notch">
                                            <label for="name" class="mdc-floating-label">Nazwa reklamy</label>
                                        </div>
                                        <div class="mdc-notched-outline__trailing"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Plik graficzny -->
                            <div class="form-grid-full">
                                <label>Plik graficzny</label>
                                <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(this)">
                                <img id="imagePreview" class="image-preview" style="display: none;">
                            </div>
                            
                            <!-- Data włączenia -->
                            <div>
                                <div class="mdc-text-field mdc-text-field--outlined" style="width: 100%;">
                                    <input type="date" id="start_date" name="start_date" class="mdc-text-field__input" required>
                                    <div class="mdc-notched-outline">
                                        <div class="mdc-notched-outline__leading"></div>
                                        <div class="mdc-notched-outline__notch">
                                            <label for="start_date" class="mdc-floating-label">Data włączenia</label>
                                        </div>
                                        <div class="mdc-notched-outline__trailing"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Data wyłączenia -->
                            <div>
                                <div class="mdc-text-field mdc-text-field--outlined" style="width: 100%;">
                                    <input type="date" id="end_date" name="end_date" class="mdc-text-field__input" required>
                                    <div class="mdc-notched-outline">
                                        <div class="mdc-notched-outline__leading"></div>
                                        <div class="mdc-notched-outline__notch">
                                            <label for="end_date" class="mdc-floating-label">Data wyłączenia</label>
                                        </div>
                                        <div class="mdc-notched-outline__trailing"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Wybór katalogów i odbiorców -->
                        <div class="selection-lists">
                            <!-- Katalogi -->
                            <div class="selection-list">
                                <h4>Wybierz katalogi</h4>
                                <?php 
                                $parentCatalogs = array_filter($catalogs, function($c) { return $c['parent_id'] === null; });
                                foreach ($parentCatalogs as $parent): 
                                ?>
                                <div class="mdc-form-field">
                                    <div class="mdc-checkbox">
                                        <input type="checkbox"
                                               class="mdc-checkbox__native-control"
                                               id="catalog_<?php echo $parent['id']; ?>"
                                               name="catalogs[]"
                                               value="<?php echo $parent['id']; ?>">
                                        <div class="mdc-checkbox__background">
                                            <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                            </svg>
                                            <div class="mdc-checkbox__mixedmark"></div>
                                        </div>
                                    </div>
                                    <label for="catalog_<?php echo $parent['id']; ?>">
                                        <?php echo escape($parent['name']); ?>
                                    </label>
                                </div>
                                
                                <?php
                                $children = array_filter($catalogs, function($c) use ($parent) { 
                                    return $c['parent_id'] == $parent['id']; 
                                });
                                if (!empty($children)):
                                ?>
                                <div class="catalog-tree">
                                    <?php foreach ($children as $child): ?>
                                    <div class="mdc-form-field">
                                        <div class="mdc-checkbox">
                                            <input type="checkbox"
                                                   class="mdc-checkbox__native-control"
                                                   id="catalog_<?php echo $child['id']; ?>"
                                                   name="catalogs[]"
                                                   value="<?php echo $child['id']; ?>">
                                            <div class="mdc-checkbox__background">
                                                <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                    <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                                </svg>
                                                <div class="mdc-checkbox__mixedmark"></div>
                                            </div>
                                        </div>
                                        <label for="catalog_<?php echo $child['id']; ?>">
                                            <?php echo escape($child['name']); ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Odbiorcy -->
                            <div class="selection-list">
                                <h4>Wybierz odbiorców</h4>
                                
                                <h5 style="margin-top: 16px; margin-bottom: 8px;">Grupy klientów:</h5>
                                <?php foreach ($clientGroups as $group): ?>
                                <div class="mdc-form-field">
                                    <div class="mdc-checkbox">
                                        <input type="checkbox"
                                               class="mdc-checkbox__native-control"
                                               id="group_<?php echo md5($group); ?>"
                                               name="groups[]"
                                               value="<?php echo escape($group); ?>">
                                        <div class="mdc-checkbox__background">
                                            <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                            </svg>
                                            <div class="mdc-checkbox__mixedmark"></div>
                                        </div>
                                    </div>
                                    <label for="group_<?php echo md5($group); ?>">
                                        <?php echo escape($group); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                                
                                <h5 style="margin-top: 16px; margin-bottom: 8px;">Konkretni klienci:</h5>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($clients as $client): ?>
                                    <div class="mdc-form-field">
                                        <div class="mdc-checkbox">
                                            <input type="checkbox"
                                                   class="mdc-checkbox__native-control"
                                                   id="client_<?php echo $client['id']; ?>"
                                                   name="clients[]"
                                                   value="<?php echo $client['id']; ?>">
                                            <div class="mdc-checkbox__background">
                                                <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                    <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                                </svg>
                                                <div class="mdc-checkbox__mixedmark"></div>
                                            </div>
                                        </div>
                                        <label for="client_<?php echo $client['id']; ?>" style="font-size: 14px;">
                                            <?php echo escape($client['company_name']); ?> 
                                            (<?php echo escape($client['first_name'] . ' ' . $client['last_name']); ?>)
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="mdc-dialog__actions">
                    <button type="button" class="mdc-button mdc-dialog__button" data-mdc-dialog-action="cancel">
                        <span class="mdc-button__label">Anuluj</span>
                    </button>
                    <button type="button" class="mdc-button mdc-button--raised mdc-dialog__button" onclick="saveAd()">
                        <span class="mdc-button__label">Zapisz</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="mdc-dialog__scrim"></div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        let adDialog;
        let currentAdId = null;
        
        $(document).ready(function() {
            initializeMDC();
            
            // Inicjalizacja dialogu
            adDialog = new mdc.dialog.MDCDialog(document.querySelector('#adDialog'));
            
            // Ustaw domyślne daty
            $('#start_date').val(new Date().toISOString().split('T')[0]);
        });
        
        function showAddAdDialog() {
            currentAdId = null;
            $('#dialogTitle').text('Dodaj reklamę');
            $('#adForm')[0].reset();
            $('#imagePreview').hide();
            $('#ad_id').val('');
            adDialog.open();
        }
        
        function editAd(adId) {
            currentAdId = adId;
            $('#dialogTitle').text('Edytuj reklamę');
            
            // Załaduj dane reklamy
            $.ajax({
                url: '../api/admin/get_ad.php',
                method: 'GET',
                data: { ad_id: adId },
                success: function(response) {
                    if (response.success) {
                        const ad = response.data;
                        $('#ad_id').val(ad.id);
                        $('#name').val(ad.name);
                        $('#start_date').val(ad.start_date);
                        $('#end_date').val(ad.end_date);
                        
                        // Pokaż obecny obrazek
                        if (ad.image_path) {
                            $('#imagePreview').attr('src', ad.image_path).show();
                        }
                        
                        // Zaznacz katalogi
                        $('input[name="catalogs[]"]').prop('checked', false);
                        ad.catalogs.forEach(catalogId => {
                            $(`#catalog_${catalogId}`).prop('checked', true);
                        });
                        
                        // Zaznacz grupy
                        $('input[name="groups[]"]').prop('checked', false);
                        ad.groups.forEach(group => {
                            $(`#group_${md5(group)}`).prop('checked', true);
                        });
                        
                        // Zaznacz klientów
                        $('input[name="clients[]"]').prop('checked', false);
                        ad.clients.forEach(clientId => {
                            $(`#client_${clientId}`).prop('checked', true);
                        });
                        
                        adDialog.open();
                    }
                }
            });
        }
        
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function saveAd() {
            const formData = new FormData($('#adForm')[0]);
            
            $.ajax({
                url: '../api/admin/save_ad.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Reklama została zapisana');
                        adDialog.close();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('error', response.message || 'Błąd podczas zapisywania');
                    }
                }
            });
        }
        
        function toggleAdStatus(adId, newStatus) {
            $.ajax({
                url: '../api/admin/toggle_ad_status.php',
                method: 'POST',
                data: {
                    ad_id: adId,
                    is_active: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Status reklamy został zmieniony');
                        setTimeout(() => location.reload(), 1000);
                    }
                }
            });
        }
        
        function deleteAd(adId) {
            if (!confirm('Czy na pewno chcesz usunąć tę reklamę?')) {
                return;
            }
            
            $.ajax({
                url: '../api/admin/delete_ad.php',
                method: 'POST',
                data: { ad_id: adId },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Reklama została usunięta');
                        setTimeout(() => location.reload(), 1000);
                    }
                }
            });
        }
        
        // Funkcja pomocnicza do generowania MD5 (symulacja)
        function md5(str) {
            return btoa(str).replace(/[^a-zA-Z0-9]/g, '').substr(0, 32);
        }
    </script>
</body>
</html>totime($ad['start_date'])); ?>
                            </div>
                            <div class="ad-date">
                                <i class="material-icons" style="font-size: 16px;">event_busy</i>
                                <?php echo date('d.m.Y',