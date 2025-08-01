<?php
// admin/catalogs.php - Zarządzanie katalogami
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireAdmin();
checkSessionTimeout();

$conn = getDBConnection();

// Pobierz wszystkie katalogi
$catalogs = fetchAll($conn, "
    SELECT c1.*, c2.name as parent_name,
           (SELECT COUNT(*) FROM catalog_files WHERE catalog_id = c1.id) as files_count
    FROM catalogs c1
    LEFT JOIN catalogs c2 ON c1.parent_id = c2.id
    ORDER BY c1.parent_id, c1.name
");

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

// Zapisz aktywność
logActivity($conn, $_SESSION['user_id'], 'view_catalogs_admin', 'admin_catalogs');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Zarządzanie katalogami - Panel Administracyjny</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .catalog-tree {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .catalog-item {
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .catalog-item:last-child {
            border-bottom: none;
        }
        
        .catalog-parent {
            font-weight: 500;
            font-size: 16px;
        }
        
        .catalog-children {
            margin-left: 32px;
            margin-top: 12px;
        }
        
        .catalog-child {
            padding: 8px 0;
            font-size: 14px;
        }
        
        .catalog-actions {
            float: right;
            display: flex;
            gap: 8px;
        }
        
        .catalog-info {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 4px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .catalog-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4caf50;
        }
        
        .status-dot.inactive {
            background: #f44336;
        }
        
        .add-catalog-section {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .quick-add-form {
            display: flex;
            gap: 16px;
            align-items: flex-end;
        }
        
        .file-upload-area {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 32px;
            text-align: center;
            margin-top: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-upload-area:hover {
            border-color: var(--primary-color);
            background: #f5f5f5;
        }
        
        .file-upload-area.drag-over {
            border-color: var(--primary-color);
            background: #e3f2fd;
        }
        
        .uploaded-files {
            margin-top: 16px;
        }
        
        .uploaded-file {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            background: #f5f5f5;
            border-radius: 4px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body class="mdc-typography admin">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Zarządzanie katalogami</h1>
                <button class="mdc-button mdc-button--raised" onclick="showAddCatalogDialog()">
                    <i class="material-icons mdc-button__icon">add</i>
                    <span class="mdc-button__label">Dodaj katalog</span>
                </button>
            </div>
            
            <!-- Szybkie dodawanie -->
            <div class="add-catalog-section">
                <h3>Szybkie dodawanie katalogu</h3>
                <form id="quickAddForm" class="quick-add-form">
                    <div class="mdc-text-field mdc-text-field--outlined" style="flex: 1;">
                        <input type="text" id="quick_name" name="name" class="mdc-text-field__input" required>
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="quick_name" class="mdc-floating-label">Nazwa katalogu</label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                    
                    <div class="mdc-select mdc-select--outlined" style="width: 300px;">
                        <div class="mdc-select__anchor">
                            <span class="mdc-notched-outline">
                                <span class="mdc-notched-outline__leading"></span>
                                <span class="mdc-notched-outline__notch">
                                    <span class="mdc-floating-label">Katalog nadrzędny</span>
                                </span>
                                <span class="mdc-notched-outline__trailing"></span>
                            </span>
                            <span class="mdc-select__selected-text"></span>
                            <span class="mdc-select__dropdown-icon">
                                <i class="material-icons">arrow_drop_down</i>
                            </span>
                        </div>
                        <div class="mdc-select__menu mdc-menu mdc-menu-surface">
                            <ul class="mdc-list">
                                <li class="mdc-list-item mdc-list-item--selected" data-value="">
                                    <span class="mdc-list-item__text">-- Katalog główny --</span>
                                </li>
                                <?php foreach ($catalogTree as $parent): ?>
                                <li class="mdc-list-item" data-value="<?php echo $parent['id']; ?>">
                                    <span class="mdc-list-item__text"><?php echo escape($parent['name']); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <input type="hidden" name="parent_id">
                    </div>
                    
                    <button type="submit" class="mdc-button mdc-button--raised">
                        <span class="mdc-button__label">Dodaj</span>
                    </button>
                </form>
            </div>
            
            <!-- Lista katalogów -->
            <div class="catalog-tree">
                <h3>Struktura katalogów</h3>
                
                <?php if (empty($catalogTree)): ?>
                <p class="text-center text-muted">Brak katalogów. Dodaj pierwszy katalog.</p>
                <?php else: ?>
                    <?php foreach ($catalogTree as $parentId => $parent): ?>
                    <div class="catalog-item">
                        <div class="catalog-actions">
                            <button class="mdc-icon-button material-icons" 
                                    title="Dodaj pliki"
                                    onclick="showFileUploadDialog(<?php echo $parent['id']; ?>)">
                                attach_file
                            </button>
                            <button class="mdc-icon-button material-icons" 
                                    title="Edytuj"
                                    onclick="editCatalog(<?php echo $parent['id']; ?>)">
                                edit
                            </button>
                            <button class="mdc-icon-button material-icons" 
                                    title="<?php echo $parent['is_active'] ? 'Dezaktywuj' : 'Aktywuj'; ?>"
                                    onclick="toggleCatalog(<?php echo $parent['id']; ?>, <?php echo $parent['is_active'] ? '0' : '1'; ?>)">
                                <?php echo $parent['is_active'] ? 'visibility_off' : 'visibility'; ?>
                            </button>
                            <button class="mdc-icon-button material-icons" 
                                    title="Usuń"
                                    onclick="deleteCatalog(<?php echo $parent['id']; ?>)">
                                delete
                            </button>
                        </div>
                        
                        <div class="catalog-parent">
                            <i class="material-icons" style="vertical-align: middle;">folder</i>
                            <?php echo escape($parent['name']); ?>
                        </div>
                        
                        <div class="catalog-info">
                            <span class="catalog-status">
                                <span class="status-dot <?php echo $parent['is_active'] ? '' : 'inactive'; ?>"></span>
                                <?php echo $parent['is_active'] ? 'Aktywny' : 'Nieaktywny'; ?>
                            </span>
                            <span>
                                <i class="material-icons" style="font-size: 16px;">insert_drive_file</i>
                                <?php echo $parent['files_count']; ?> plików
                            </span>
                        </div>
                        
                        <?php if (isset($childCatalogs[$parentId])): ?>
                        <div class="catalog-children">
                            <?php foreach ($childCatalogs[$parentId] as $child): ?>
                            <div class="catalog-child">
                                <div class="catalog-actions">
                                    <button class="mdc-icon-button material-icons" 
                                            title="<?php echo $child['is_active'] ? 'Dezaktywuj' : 'Aktywuj'; ?>"
                                            onclick="toggleCatalog(<?php echo $child['id']; ?>, <?php echo $child['is_active'] ? '0' : '1'; ?>)">
                                        <?php echo $child['is_active'] ? 'visibility_off' : 'visibility'; ?>
                                    </button>
                                    <button class="mdc-icon-button material-icons" 
                                            title="Usuń"
                                            onclick="deleteCatalog(<?php echo $child['id']; ?>)">
                                        delete
                                    </button>
                                </div>
                                
                                <div>
                                    <i class="material-icons" style="font-size: 18px; vertical-align: middle;">subdirectory_arrow_right</i>
                                    <?php echo escape($child['name']); ?>
                                    
                                    <span class="catalog-info" style="display: inline; margin-left: 16px;">
                                        <span class="catalog-status">
                                            <span class="status-dot <?php echo $child['is_active'] ? '' : 'inactive'; ?>"></span>
                                            <?php echo $child['is_active'] ? 'Aktywny' : 'Nieaktywny'; ?>
                                        </span>
                                        <span style="margin-left: 16px;">
                                            <i class="material-icons" style="font-size: 16px;">insert_drive_file</i>
                                            <?php echo $child['files_count']; ?> plików
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Dialog dodawania katalogu -->
    <div class="mdc-dialog" id="addCatalogDialog">
        <div class="mdc-dialog__container">
            <div class="mdc-dialog__surface">
                <h2 class="mdc-dialog__title">Dodaj nowy katalog</h2>
                <div class="mdc-dialog__content">
                    <form id="addCatalogForm">
                        <div class="form-field">
                            <div class="mdc-radio">
                                <input class="mdc-radio__native-control" type="radio" id="type_main" name="catalog_type" value="main" checked>
                                <div class="mdc-radio__background">
                                    <div class="mdc-radio__outer-circle"></div>
                                    <div class="mdc-radio__inner-circle"></div>
                                </div>
                            </div>
                            <label for="type_main">Katalog główny</label>
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-radio">
                                <input class="mdc-radio__native-control" type="radio" id="type_sub" name="catalog_type" value="sub">
                                <div class="mdc-radio__background">
                                    <div class="mdc-radio__outer-circle"></div>
                                    <div class="mdc-radio__inner-circle"></div>
                                </div>
                            </div>
                            <label for="type_sub">Podkatalog</label>
                        </div>
                        
                        <div id="parent_select_container" style="display: none; margin-top: 16px;">
                            <select name="parent_id" class="mdc-select__native-control">
                                <option value="">-- Wybierz katalog nadrzędny --</option>
                                <?php foreach ($catalogTree as $parent): ?>
                                <option value="<?php echo $parent['id']; ?>"><?php echo escape($parent['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mdc-text-field mdc-text-field--outlined" style="width: 100%; margin-top: 16px;">
                            <input type="text" id="catalog_name" name="name" class="mdc-text-field__input" required>
                            <div class="mdc-notched-outline">
                                <div class="mdc-notched-outline__leading"></div>
                                <div class="mdc-notched-outline__notch">
                                    <label for="catalog_name" class="mdc-floating-label">Nazwa katalogu</label>
                                </div>
                                <div class="mdc-notched-outline__trailing"></div>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 24px;">Dodaj pliki (opcjonalnie)</h4>
                        
                        <div class="form-field">
                            <div class="mdc-checkbox">
                                <input type="checkbox" class="mdc-checkbox__native-control" id="add_instructions">
                                <div class="mdc-checkbox__background">
                                    <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                        <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                    </svg>
                                    <div class="mdc-checkbox__mixedmark"></div>
                                </div>
                            </div>
                            <label for="add_instructions">Dodaj instrukcję</label>
                        </div>
                        
                        <div id="instructions_upload" style="display: none;">
                            <input type="file" name="instructions_file" accept=".pdf,.doc,.docx">
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-checkbox">
                                <input type="checkbox" class="mdc-checkbox__native-control" id="add_installation">
                                <div class="mdc-checkbox__background">
                                    <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                        <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                    </svg>
                                    <div class="mdc-checkbox__mixedmark"></div>
                                </div>
                            </div>
                            <label for="add_installation">Dodaj instrukcję instalacji</label>
                        </div>
                        
                        <div id="installation_upload" style="display: none;">
                            <input type="file" name="installation_file" accept=".pdf,.doc,.docx">
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-checkbox">
                                <input type="checkbox" class="mdc-checkbox__native-control" id="add_images">
                                <div class="mdc-checkbox__background">
                                    <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                        <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                    </svg>
                                    <div class="mdc-checkbox__mixedmark"></div>
                                </div>
                            </div>
                            <label for="add_images">Dodaj zdjęcia</label>
                        </div>
                        
                        <div id="images_upload" style="display: none;">
                            <input type="file" name="images_files[]" accept="image/*" multiple>
                            <small>Maksymalnie 25 plików, każdy do 5MB</small>
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-checkbox">
                                <input type="checkbox" class="mdc-checkbox__native-control" id="add_video">
                                <div class="mdc-checkbox__background">
                                    <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                        <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                    </svg>
                                    <div class="mdc-checkbox__mixedmark"></div>
                                </div>
                            </div>
                            <label for="add_video">Dodaj wideo</label>
                        </div>
                        
                        <div id="video_upload" style="display: none;">
                            <input type="file" name="video_file" accept="video/*">
                            <small>Maksymalnie 50MB</small>
                        </div>
                    </form>
                </div>
                <div class="mdc-dialog__actions">
                    <button type="button" class="mdc-button mdc-dialog__button" data-mdc-dialog-action="cancel">
                        <span class="mdc-button__label">Anuluj</span>
                    </button>
                    <button type="button" class="mdc-button mdc-button--raised mdc-dialog__button" onclick="submitCatalogForm()">
                        <span class="mdc-button__label">Dodaj katalog</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="mdc-dialog__scrim"></div>
    </div>
    
    <!-- Dialog dodawania plików -->
    <div class="mdc-dialog" id="fileUploadDialog">
        <div class="mdc-dialog__container">
            <div class="mdc-dialog__surface" style="width: 600px;">
                <h2 class="mdc-dialog__title">Dodaj pliki do katalogu</h2>
                <div class="mdc-dialog__content">
                    <form id="fileUploadForm">
                        <input type="hidden" name="catalog_id" id="upload_catalog_id">
                        
                        <div class="file-upload-area" id="dropZone">
                            <i class="material-icons" style="font-size: 48px; color: #9e9e9e;">cloud_upload</i>
                            <p>Przeciągnij pliki tutaj lub kliknij aby wybrać</p>
                            <input type="file" id="fileInput" multiple style="display: none;">
                        </div>
                        
                        <div class="uploaded-files" id="uploadedFiles"></div>
                    </form>
                </div>
                <div class="mdc-dialog__actions">
                    <button type="button" class="mdc-button mdc-dialog__button" data-mdc-dialog-action="cancel">
                        <span class="mdc-button__label">Anuluj</span>
                    </button>
                    <button type="button" class="mdc-button mdc-button--raised mdc-dialog__button" onclick="uploadFiles()">
                        <span class="mdc-button__label">Prześlij pliki</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="mdc-dialog__scrim"></div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        let addCatalogDialog;
        let fileUploadDialog;
        let selectedFiles = [];
        
        $(document).ready(function() {
            initializeMDC();
            
            // Inicjalizacja dialogów
            addCatalogDialog = new mdc.dialog.MDCDialog(document.querySelector('#addCatalogDialog'));
            fileUploadDialog = new mdc.dialog.MDCDialog(document.querySelector('#fileUploadDialog'));
            
            // Obsługa typu katalogu
            $('input[name="catalog_type"]').on('change', function() {
                if ($(this).val() === 'sub') {
                    $('#parent_select_container').show();
                } else {
                    $('#parent_select_container').hide();
                }
            });
            
            // Obsługa checkboxów plików
            $('#add_instructions').on('change', function() {
                $('#instructions_upload').toggle(this.checked);
            });
            
            $('#add_installation').on('change', function() {
                $('#installation_upload').toggle(this.checked);
            });
            
            $('#add_images').on('change', function() {
                $('#images_upload').toggle(this.checked);
            });
            
            $('#add_video').on('change', function() {
                $('#video_upload').toggle(this.checked);
            });
            
            // Szybkie dodawanie
            $('#quickAddForm').on('submit', function(e) {
                e.preventDefault();
                
                const data = {
                    name: $('#quick_name').val(),
                    parent_id: $('input[name="parent_id"]').val() || null
                };
                
                $.ajax({
                    url: '../api/admin/catalog_create.php',
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', 'Katalog został dodany');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification('error', response.message || 'Błąd podczas dodawania katalogu');
                        }
                    }
                });
            });
            
            // Drag & Drop
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            
            dropZone.addEventListener('click', () => fileInput.click());
            
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            });
            
            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('drag-over');
            });
            
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
                handleFiles(e.dataTransfer.files);
            });
            
            fileInput.addEventListener('change', (e) => {
                handleFiles(e.target.files);
            });
        });
        
        function showAddCatalogDialog() {
            addCatalogDialog.open();
        }
        
        function showFileUploadDialog(catalogId) {
            $('#upload_catalog_id').val(catalogId);
            selectedFiles = [];
            $('#uploadedFiles').empty();
            fileUploadDialog.open();
        }
        
        function handleFiles(files) {
            for (let file of files) {
                selectedFiles.push(file);
                displayFile(file);
            }
        }
        
        function displayFile(file) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const html = `
                <div class="uploaded-file">
                    <i class="material-icons">insert_drive_file</i>
                    <span style="flex: 1;">${file.name} (${fileSize} MB)</span>
                    <button class="mdc-icon-button material-icons" onclick="removeFile('${file.name}')">close</button>
                </div>
            `;
            $('#uploadedFiles').append(html);
        }
        
        function removeFile(fileName) {
            selectedFiles = selectedFiles.filter(f => f.name !== fileName);
            displayAllFiles();
        }
        
        function displayAllFiles() {
            $('#uploadedFiles').empty();
            selectedFiles.forEach(file => displayFile(file));
        }
        
        function toggleCatalog(catalogId, newStatus) {
            $.ajax({
                url: '../api/admin/catalog_toggle.php',
                method: 'POST',
                data: {
                    catalog_id: catalogId,
                    is_active: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Status katalogu został zmieniony');
                        setTimeout(() => location.reload(), 1000);
                    }
                }
            });
        }
        
        function deleteCatalog(catalogId) {
            if (!confirm('Czy na pewno chcesz usunąć ten katalog? Wszystkie pliki zostaną również usunięte.')) {
                return;
            }
            
            $.ajax({
                url: '../api/admin/catalog_delete.php',
                method: 'POST',
                data: { catalog_id: catalogId },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Katalog został usunięty');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('error', response.message || 'Błąd podczas usuwania');
                    }
                }
            });
        }
        
        function submitCatalogForm() {
            const formData = new FormData($('#addCatalogForm')[0]);
            
            $.ajax({
                url: '../api/admin/catalog_create_full.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Katalog został utworzony');
                        addCatalogDialog.close();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('error', response.message || 'Błąd podczas tworzenia katalogu');
                    }
                }
            });
        }
        
        function uploadFiles() {
            if (selectedFiles.length === 0) {
                showNotification('warning', 'Wybierz pliki do przesłania');
                return;
            }
            
            const formData = new FormData();
            formData.append('catalog_id', $('#upload_catalog_id').val());
            
            selectedFiles.forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });
            
            $.ajax({
                url: '../api/admin/catalog_upload_files.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = evt.loaded / evt.total * 100;
                            console.log('Upload progress:', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Pliki zostały przesłane');
                        fileUploadDialog.close();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('error', response.message || 'Błąd podczas przesyłania plików');
                    }
                }
            });
        }
    </script>
</body>
</html>="Dodaj pliki"
                                            onclick="showFileUploadDialog(<?php echo $child['id']; ?>)">
                                        attach_file
                                    </button>
                                    <button class="mdc-icon-button material-icons" 
                                            title="Edytuj"
                                            onclick="editCatalog(<?php echo $child['id']; ?>)">
                                        edit
                                    </button>
                                    <button class="mdc-icon-button material-icons" 
                                            title