<?php
// admin/files.php - Zarządzanie plikami klientów
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireAdmin();
checkSessionTimeout();

$conn = getDBConnection();

// Pobierz listę klientów
$clients = fetchAll($conn, "
    SELECT u.id, u.email, cd.company_name, cd.first_name, cd.last_name,
           (SELECT COUNT(*) FROM client_files WHERE user_id = u.id) as files_count,
           (SELECT SUM(file_size) FROM client_files WHERE user_id = u.id) as total_size
    FROM users u
    JOIN company_data cd ON u.id = cd.user_id
    WHERE u.user_type IN ('client', 'subaccount')
    ORDER BY cd.company_name
");

// Zapisz aktywność
logActivity($conn, $_SESSION['user_id'], 'view_files_admin', 'admin_files');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Zarządzanie plikami - Panel Administracyjny</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .client-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }
        
        .client-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .client-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .client-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .client-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .client-avatar i {
            font-size: 24px;
            color: var(--primary-color);
        }
        
        .client-info {
            flex: 1;
        }
        
        .client-name {
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .client-company {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .client-stats {
            display: flex;
            justify-content: space-between;
            padding-top: 16px;
            border-top: 1px solid #f5f5f5;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        /* Panel plików klienta */
        .files-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: 600px;
            height: 100%;
            background: white;
            box-shadow: -2px 0 8px rgba(0,0,0,0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1100;
            display: flex;
            flex-direction: column;
        }
        
        .files-panel.active {
            transform: translateX(0);
        }
        
        .files-panel-header {
            padding: 24px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .files-panel-content {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }
        
        .upload-section {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .folder-section {
            margin-bottom: 32px;
        }
        
        .folder-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-weight: 500;
        }
        
        .file-list {
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .file-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
            gap: 12px;
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
            margin-bottom: 2px;
        }
        
        .file-info {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .drop-zone {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 32px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .drop-zone:hover {
            border-color: var(--primary-color);
            background: #fafafa;
        }
        
        .drop-zone.drag-over {
            border-color: var(--primary-color);
            background: #e3f2fd;
        }
        
        .search-section {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="mdc-typography admin">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Zarządzanie plikami klientów</h1>
            </div>
            
            <!-- Wyszukiwarka -->
            <div class="search-section">
                <div class="mdc-text-field mdc-text-field--outlined mdc-text-field--with-leading-icon">
                    <i class="material-icons mdc-text-field__icon">search</i>
                    <input type="text" 
                           id="searchClients" 
                           class="mdc-text-field__input" 
                           placeholder="Szukaj klienta...">
                    <div class="mdc-notched-outline">
                        <div class="mdc-notched-outline__leading"></div>
                        <div class="mdc-notched-outline__notch">
                            <label class="mdc-floating-label">Wyszukaj klienta</label>
                        </div>
                        <div class="mdc-notched-outline__trailing"></div>
                    </div>
                </div>
            </div>
            
            <!-- Lista klientów -->
            <div class="client-grid" id="clientGrid">
                <?php foreach ($clients as $client): ?>
                <div class="client-card" data-client-id="<?php echo $client['id']; ?>" 
                     data-search="<?php echo strtolower($client['company_name'] . ' ' . $client['first_name'] . ' ' . $client['last_name'] . ' ' . $client['email']); ?>">
                    <div class="client-header">
                        <div class="client-avatar">
                            <i class="material-icons">business</i>
                        </div>
                        <div class="client-info">
                            <div class="client-name">
                                <?php echo escape($client['first_name'] . ' ' . $client['last_name']); ?>
                            </div>
                            <div class="client-company">
                                <?php echo escape($client['company_name']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="client-stats">
                        <div class="stat">
                            <div class="stat-value"><?php echo $client['files_count'] ?? 0; ?></div>
                            <div class="stat-label">Pliki</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value"><?php echo formatFileSize($client['total_size'] ?? 0); ?></div>
                            <div class="stat-label">Rozmiar</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    
    <!-- Panel plików klienta -->
    <div class="files-panel" id="filesPanel">
        <div class="files-panel-header">
            <div>
                <h2 id="clientPanelName">Pliki klienta</h2>
                <p id="clientPanelCompany" style="margin: 0; color: var(--text-secondary);"></p>
            </div>
            <button class="mdc-icon-button material-icons" onclick="closeFilesPanel()">close</button>
        </div>
        
        <div class="files-panel-content">
            <!-- Sekcja uploadu -->
            <div class="upload-section">
                <h3>Dodaj pliki</h3>
                
                <div class="form-field" style="margin-bottom: 16px;">
                    <select id="uploadFolder" class="mdc-select__native-control" style="width: 100%; padding: 8px;">
                        <option value="faktury">Faktury</option>
                        <option value="dane">Dane i pliki klienta</option>
                        <option value="">Inny folder (wpisz poniżej)</option>
                    </select>
                </div>
                
                <div class="mdc-text-field mdc-text-field--outlined" id="customFolderField" style="width: 100%; margin-bottom: 16px; display: none;">
                    <input type="text" id="customFolder" class="mdc-text-field__input">
                    <div class="mdc-notched-outline">
                        <div class="mdc-notched-outline__leading"></div>
                        <div class="mdc-notched-outline__notch">
                            <label class="mdc-floating-label">Nazwa folderu</label>
                        </div>
                        <div class="mdc-notched-outline__trailing"></div>
                    </div>
                </div>
                
                <div class="drop-zone" id="dropZone">
                    <i class="material-icons" style="font-size: 48px; color: #9e9e9e;">cloud_upload</i>
                    <p>Przeciągnij pliki tutaj lub kliknij aby wybrać</p>
                    <input type="file" id="fileInput" multiple style="display: none;">
                </div>
                
                <div id="uploadQueue" style="margin-top: 16px;"></div>
                
                <button class="mdc-button mdc-button--raised" id="uploadButton" style="width: 100%; margin-top: 16px;" disabled>
                    <i class="material-icons mdc-button__icon">upload</i>
                    <span class="mdc-button__label">Prześlij pliki</span>
                </button>
            </div>
            
            <!-- Lista plików -->
            <div id="clientFiles"></div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        let currentClientId = null;
        let selectedFiles = [];
        
        $(document).ready(function() {
            initializeMDC();
            
            // Wyszukiwanie klientów
            $('#searchClients').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                $('.client-card').each(function() {
                    const searchData = $(this).data('search');
                    if (searchData.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
            
            // Kliknięcie w kartę klienta
            $('.client-card').on('click', function() {
                const clientId = $(this).data('client-id');
                const clientName = $(this).find('.client-name').text();
                const clientCompany = $(this).find('.client-company').text();
                
                openFilesPanel(clientId, clientName, clientCompany);
            });
            
            // Wybór folderu
            $('#uploadFolder').on('change', function() {
                if ($(this).val() === '') {
                    $('#customFolderField').show();
                } else {
                    $('#customFolderField').hide();
                }
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
            
            // Upload plików
            $('#uploadButton').on('click', uploadFiles);
        });
        
        function openFilesPanel(clientId, clientName, clientCompany) {
            currentClientId = clientId;
            $('#clientPanelName').text('Pliki - ' + clientName);
            $('#clientPanelCompany').text(clientCompany);
            $('#filesPanel').addClass('active');
            
            // Załaduj pliki klienta
            loadClientFiles(clientId);
            
            // Wyczyść kolejkę uploadu
            selectedFiles = [];
            $('#uploadQueue').empty();
            $('#uploadButton').prop('disabled', true);
        }
        
        function closeFilesPanel() {
            $('#filesPanel').removeClass('active');
            currentClientId = null;
        }
        
        function loadClientFiles(clientId) {
            $.ajax({
                url: '../api/admin/get_client_files.php',
                method: 'GET',
                data: { client_id: clientId },
                success: function(response) {
                    displayClientFiles(response);
                }
            });
        }
        
        function displayClientFiles(files) {
            const container = $('#clientFiles');
            container.empty();
            
            if (!files || files.length === 0) {
                container.html('<p class="text-center text-muted">Brak plików</p>');
                return;
            }
            
            // Grupuj pliki według folderów
            const filesByFolder = {};
            files.forEach(file => {
                if (!filesByFolder[file.folder_name]) {
                    filesByFolder[file.folder_name] = [];
                }
                filesByFolder[file.folder_name].push(file);
            });
            
            // Wyświetl foldery
            Object.keys(filesByFolder).forEach(folderName => {
                const folderHtml = `
                    <div class="folder-section">
                        <div class="folder-header">
                            <i class="material-icons">folder</i>
                            <span>${folderName}</span>
                            <span style="color: var(--text-secondary); margin-left: auto;">
                                ${filesByFolder[folderName].length} plików
                            </span>
                        </div>
                        <div class="file-list">
                            ${filesByFolder[folderName].map(file => `
                                <div class="file-item">
                                    <i class="material-icons file-icon">${getFileIcon(file.file_name)}</i>
                                    <div class="file-details">
                                        <div class="file-name">${file.file_name}</div>
                                        <div class="file-info">
                                            ${formatFileSize(file.file_size)} • 
                                            ${new Date(file.created_at).toLocaleDateString('pl-PL')}
                                        </div>
                                    </div>
                                    <button class="mdc-icon-button material-icons" 
                                            onclick="deleteFile(${file.id})"
                                            title="Usuń">
                                        delete
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
                container.append(folderHtml);
            });
        }
        
        function handleFiles(files) {
            for (let file of files) {
                selectedFiles.push(file);
                displayFile(file);
            }
            $('#uploadButton').prop('disabled', selectedFiles.length === 0);
        }
        
        function displayFile(file) {
            const fileSize = formatFileSize(file.size);
            const html = `
                <div class="uploaded-file" style="display: flex; align-items: center; gap: 8px; padding: 8px; background: #f5f5f5; border-radius: 4px; margin-bottom: 8px;">
                    <i class="material-icons">insert_drive_file</i>
                    <span style="flex: 1;">${file.name} (${fileSize})</span>
                    <button class="mdc-icon-button material-icons" onclick="removeFromQueue('${file.name}')">close</button>
                </div>
            `;
            $('#uploadQueue').append(html);
        }
        
        function removeFromQueue(fileName) {
            selectedFiles = selectedFiles.filter(f => f.name !== fileName);
            displayAllFiles();
        }
        
        function displayAllFiles() {
            $('#uploadQueue').empty();
            selectedFiles.forEach(file => displayFile(file));
            $('#uploadButton').prop('disabled', selectedFiles.length === 0);
        }
        
        function uploadFiles() {
            if (!currentClientId || selectedFiles.length === 0) return;
            
            const folder = $('#uploadFolder').val() || $('#customFolder').val() || 'dane';
            const formData = new FormData();
            
            formData.append('client_id', currentClientId);
            formData.append('folder', folder);
            
            selectedFiles.forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });
            
            $.ajax({
                url: '../api/admin/upload_client_files.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#uploadButton').prop('disabled', true).html('<span class="loading-spinner"></span> Przesyłanie...');
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Pliki zostały przesłane');
                        selectedFiles = [];
                        $('#uploadQueue').empty();
                        $('#fileInput').val('');
                        loadClientFiles(currentClientId);
                        
                        // Odśwież licznik plików
                        updateClientCard(currentClientId);
                    } else {
                        showNotification('error', response.message || 'Błąd przesyłania plików');
                    }
                },
                complete: function() {
                    $('#uploadButton').html('<i class="material-icons mdc-button__icon">upload</i><span class="mdc-button__label">Prześlij pliki</span>');
                }
            });
        }
        
        function deleteFile(fileId) {
            if (!confirm('Czy na pewno chcesz usunąć ten plik?')) return;
            
            $.ajax({
                url: '../api/admin/delete_client_file.php',
                method: 'POST',
                data: { file_id: fileId },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Plik został usunięty');
                        loadClientFiles(currentClientId);
                        updateClientCard(currentClientId);
                    } else {
                        showNotification('error', response.message || 'Błąd usuwania pliku');
                    }
                }
            });
        }
        
        function updateClientCard(clientId) {
            // Odśwież statystyki klienta
            $.ajax({
                url: '../api/admin/get_client_stats.php',
                method: 'GET',
                data: { client_id: clientId },
                success: function(stats) {
                    const card = $(`.client-card[data-client-id="${clientId}"]`);
                    card.find('.stat-value').first().text(stats.files_count);
                    card.find('.stat-value').last().text(formatFileSize(stats.total_size));
                }
            });
        }
        
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                'pdf': 'picture_as_pdf',
                'doc': 'description',
                'docx': 'description',
                'xls': 'table_chart',
                'xlsx': 'table_chart',
                'jpg': 'image',
                'jpeg': 'image',
                'png': 'image',
                'zip': 'folder_zip',
                'rar': 'folder_zip'
            };
            return icons[ext] || 'insert_drive_file';
        }
        
        function formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>