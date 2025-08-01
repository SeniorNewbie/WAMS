<?php
// client/subaccounts.php - Zarządzanie subkontami
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireClient();
checkSessionTimeout();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Sprawdź czy to konto główne
if ($_SESSION['user_type'] !== 'client') {
    header('Location: ../dashboard.php');
    exit();
}

// Pobierz subkonta
$stmt = $conn->prepare("
    SELECT u.*, cd.*, 
           (SELECT last_login FROM users WHERE id = u.id) as last_login,
           (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as orders_count
    FROM users u
    JOIN company_data cd ON u.id = cd.user_id
    WHERE u.parent_id = ?
    ORDER BY u.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$subaccounts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pobierz uprawnienia do modułów
$products = [
    'Moskitiera ramkowa',
    'Moskitiera drzwiowa', 
    'Moskitiera przesuwna',
    'Moskitiera plisowana',
    'Roleta zewnętrzna',
    'Roleta wewnętrzna',
    'Żaluzja pozioma',
    'Żaluzja pionowa'
];

// Zapisz aktywność
logActivity($conn, $userId, 'view_subaccounts', 'subaccounts');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Subkonta - Panel Klienta</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .subaccounts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }
        
        .subaccount-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .subaccount-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .subaccount-info {
            flex: 1;
        }
        
        .subaccount-name {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .subaccount-email {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .subaccount-status {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 14px;
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
        
        .subaccount-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin: 16px 0;
            padding: 16px 0;
            border-top: 1px solid #f5f5f5;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        
        .subaccount-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .empty-state {
            text-align: center;
            padding: 64px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
        }
        
        /* Dialog form */
        .form-section {
            margin-bottom: 24px;
        }
        
        .form-section-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        
        .permissions-grid {
            display: grid;
            gap: 16px;
        }
        
        .permission-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
        }
        
        .permission-header {
            font-weight: 500;
            margin-bottom: 12px;
        }
        
        .permission-controls {
            display: flex;
            gap: 24px;
        }
        
        .info-box {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .info-box i {
            color: #1976d2;
        }
    </style>
</head>
<body class="mdc-typography">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Zarządzanie subkontami</h1>
                <button class="mdc-button mdc-button--raised" onclick="showAddSubaccountDialog()">
                    <i class="material-icons mdc-button__icon">add</i>
                    <span class="mdc-button__label">Dodaj konto pracownika</span>
                </button>
            </div>
            
            <div class="info-box">
                <i class="material-icons">info</i>
                <div>
                    <strong>Informacja:</strong> Subkonta pozwalają Twoim pracownikom na dostęp do wybranych funkcji panelu. 
                    Każde subkonto może mieć indywidualne uprawnienia do przeglądania katalogów i składania zamówień.
                </div>
            </div>
            
            <?php if (empty($subaccounts)): ?>
            <div class="empty-state">
                <i class="material-icons">group_off</i>
                <h3>Brak subkont</h3>
                <p>Nie utworzyłeś jeszcze żadnych kont dla pracowników.</p>
                <button class="mdc-button mdc-button--raised" onclick="showAddSubaccountDialog()">
                    <span class="mdc-button__label">Utwórz pierwsze subkonto</span>
                </button>
            </div>
            <?php else: ?>
            <div class="subaccounts-grid">
                <?php foreach ($subaccounts as $subaccount): ?>
                <div class="subaccount-card">
                    <div class="subaccount-header">
                        <div class="subaccount-info">
                            <div class="subaccount-name">
                                <?php echo escape($subaccount['first_name'] . ' ' . $subaccount['last_name']); ?>
                            </div>
                    
                    <div class="subaccount-actions">
                        <button class="action-button secondary" onclick="editSubaccount(<?php echo $subaccount['id']; ?>)">
                            <i class="material-icons">edit</i>
                            Edytuj
                        </button>
                        <button class="action-button secondary" 
                                onclick="toggleSubaccountStatus(<?php echo $subaccount['id']; ?>, <?php echo $subaccount['is_active'] ? '0' : '1'; ?>)">
                            <i class="material-icons"><?php echo $subaccount['is_active'] ? 'block' : 'check_circle'; ?></i>
                            <?php echo $subaccount['is_active'] ? 'Dezaktywuj' : 'Aktywuj'; ?>
                        </button>
                        <button class="action-button secondary" onclick="resetPassword(<?php echo $subaccount['id']; ?>)">
                            <i class="material-icons">lock_reset</i>
                            Reset hasła
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Dialog dodawania/edycji subkonta -->
    <div class="mdc-dialog" id="subaccountDialog">
        <div class="mdc-dialog__container">
            <div class="mdc-dialog__surface" style="width: 700px; max-width: 90vw;">
                <h2 class="mdc-dialog__title" id="dialogTitle">Dodaj konto pracownika</h2>
                <div class="mdc-dialog__content">
                    <form id="subaccountForm">
                        <input type="hidden" name="subaccount_id" id="subaccount_id">
                        
                        <!-- Dane dostępowe -->
                        <div class="form-section">
                            <h3 class="form-section-title">Dane dostępowe</h3>
                            <div class="form-row">
                                <div class="form-field">
                                    <div class="mdc-text-field mdc-text-field--outlined">
                                        <input type="email" id="email" name="email" class="mdc-text-field__input" required>
                                        <div class="mdc-notched-outline">
                                            <div class="mdc-notched-outline__leading"></div>
                                            <div class="mdc-notched-outline__notch">
                                                <label for="email" class="mdc-floating-label">Adres e-mail (login)</label>
                                            </div>
                                            <div class="mdc-notched-outline__trailing"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-field" id="passwordField">
                                    <div class="mdc-text-field mdc-text-field--outlined">
                                        <input type="password" id="password" name="password" class="mdc-text-field__input">
                                        <div class="mdc-notched-outline">
                                            <div class="mdc-notched-outline__leading"></div>
                                            <div class="mdc-notched-outline__notch">
                                                <label for="password" class="mdc-floating-label">Hasło</label>
                                            </div>
                                            <div class="mdc-notched-outline__trailing"></div>
                                        </div>
                                    </div>
                                    <button type="button" class="mdc-button" onclick="generatePassword()">
                                        <i class="material-icons mdc-button__icon">refresh</i>
                                        Generuj hasło
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dane osobowe -->
                        <div class="form-section">
                            <h3 class="form-section-title">Dane osobowe</h3>
                            <div class="form-row">
                                <div class="form-field">
                                    <div class="mdc-text-field mdc-text-field--outlined">
                                        <input type="text" id="first_name" name="first_name" class="mdc-text-field__input" required>
                                        <div class="mdc-notched-outline">
                                            <div class="mdc-notched-outline__leading"></div>
                                            <div class="mdc-notched-outline__notch">
                                                <label for="first_name" class="mdc-floating-label">Imię</label>
                                            </div>
                                            <div class="mdc-notched-outline__trailing"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="mdc-text-field mdc-text-field--outlined">
                                        <input type="text" id="last_name" name="last_name" class="mdc-text-field__input" required>
                                        <div class="mdc-notched-outline">
                                            <div class="mdc-notched-outline__leading"></div>
                                            <div class="mdc-notched-outline__notch">
                                                <label for="last_name" class="mdc-floating-label">Nazwisko</label>
                                            </div>
                                            <div class="mdc-notched-outline__trailing"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Uprawnienia -->
                        <div class="form-section">
                            <h3 class="form-section-title">Uprawnienia do modułu zamówień</h3>
                            <div class="permissions-grid">
                                <?php foreach ($products as $product): ?>
                                <div class="permission-item">
                                    <div class="permission-header"><?php echo escape($product); ?></div>
                                    <div class="permission-controls">
                                        <div class="mdc-form-field">
                                            <div class="mdc-checkbox">
                                                <input type="checkbox"
                                                       class="mdc-checkbox__native-control"
                                                       id="quote_<?php echo md5($product); ?>"
                                                       name="permissions[<?php echo escape($product); ?>][quote]"
                                                       value="1">
                                                <div class="mdc-checkbox__background">
                                                    <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                        <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                                    </svg>
                                                    <div class="mdc-checkbox__mixedmark"></div>
                                                </div>
                                            </div>
                                            <label for="quote_<?php echo md5($product); ?>">Możliwość wyceny</label>
                                        </div>
                                        
                                        <div class="mdc-form-field">
                                            <div class="mdc-checkbox">
                                                <input type="checkbox"
                                                       class="mdc-checkbox__native-control"
                                                       id="order_<?php echo md5($product); ?>"
                                                       name="permissions[<?php echo escape($product); ?>][order]"
                                                       value="1">
                                                <div class="mdc-checkbox__background">
                                                    <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                        <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                                    </svg>
                                                    <div class="mdc-checkbox__mixedmark"></div>
                                                </div>
                                            </div>
                                            <label for="order_<?php echo md5($product); ?>">Możliwość zamówienia</label>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="mdc-dialog__actions">
                    <button type="button" class="mdc-button mdc-dialog__button" data-mdc-dialog-action="cancel">
                        <span class="mdc-button__label">Anuluj</span>
                    </button>
                    <button type="button" class="mdc-button mdc-button--raised mdc-dialog__button" onclick="saveSubaccount()">
                        <span class="mdc-button__label">Zapisz</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="mdc-dialog__scrim"></div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        let subaccountDialog;
        let currentSubaccountId = null;
        
        $(document).ready(function() {
            initializeMDC();
            
            // Inicjalizacja dialogu
            subaccountDialog = new mdc.dialog.MDCDialog(document.querySelector('#subaccountDialog'));
        });
        
        function showAddSubaccountDialog() {
            currentSubaccountId = null;
            $('#dialogTitle').text('Dodaj konto pracownika');
            $('#subaccountForm')[0].reset();
            $('#subaccount_id').val('');
            $('#passwordField').show();
            $('#password').prop('required', true);
            generatePassword();
            subaccountDialog.open();
        }
        
        function editSubaccount(subaccountId) {
            currentSubaccountId = subaccountId;
            $('#dialogTitle').text('Edytuj konto pracownika');
            $('#passwordField').hide();
            $('#password').prop('required', false);
            
            // Załaduj dane subkonta
            $.ajax({
                url: '../api/get_subaccount.php',
                method: 'GET',
                data: { subaccount_id: subaccountId },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#subaccount_id').val(data.id);
                        $('#email').val(data.email);
                        $('#first_name').val(data.first_name);
                        $('#last_name').val(data.last_name);
                        
                        // Zaznacz uprawnienia
                        $('input[type="checkbox"]').prop('checked', false);
                        data.permissions.forEach(perm => {
                            if (perm.can_quote) {
                                $(`#quote_${md5(perm.product_name)}`).prop('checked', true);
                            }
                            if (perm.can_order) {
                                $(`#order_${md5(perm.product_name)}`).prop('checked', true);
                            }
                        });
                        
                        subaccountDialog.open();
                    }
                }
            });
        }
        
        function generatePassword() {
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            $('#password').val(password);
        }
        
        function saveSubaccount() {
            const form = $('#subaccountForm');
            
            if (!form[0].checkValidity()) {
                showNotification('error', 'Wypełnij wszystkie wymagane pola');
                return;
            }
            
            $.ajax({
                url: '../api/save_subaccount.php',
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Subkonto zostało zapisane');
                        subaccountDialog.close();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('error', response.message || 'Błąd podczas zapisywania');
                    }
                }
            });
        }
        
        function toggleSubaccountStatus(subaccountId, newStatus) {
            const action = newStatus ? 'aktywować' : 'dezaktywować';
            
            if (!confirm(`Czy na pewno chcesz ${action} to subkonto?`)) {
                return;
            }
            
            $.ajax({
                url: '../api/toggle_subaccount_status.php',
                method: 'POST',
                data: {
                    subaccount_id: subaccountId,
                    is_active: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Status został zmieniony');
                        setTimeout(() => location.reload(), 1000);
                    }
                }
            });
        }
        
        function resetPassword(subaccountId) {
            if (!confirm('Czy na pewno chcesz zresetować hasło dla tego subkonta? Nowe hasło zostanie wysłane na adres email.')) {
                return;
            }
            
            $.ajax({
                url: '../api/reset_subaccount_password.php',
                method: 'POST',
                data: { subaccount_id: subaccountId },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Nowe hasło zostało wysłane na adres email');
                    } else {
                        showNotification('error', response.message || 'Błąd podczas resetowania hasła');
                    }
                }
            });
        }
        
        // Funkcja pomocnicza MD5
        function md5(str) {
            return btoa(str).replace(/[^a-zA-Z0-9]/g, '').substr(0, 32);
        }
    </script>
</body>
</html>
                            <div class="subaccount-email">
                                <?php echo escape($subaccount['email']); ?>
                            </div>
                            <div class="subaccount-status">
                                <span class="status-dot <?php echo $subaccount['is_active'] ? '' : 'inactive'; ?>"></span>
                                <?php echo $subaccount['is_active'] ? 'Aktywne' : 'Nieaktywne'; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="subaccount-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $subaccount['orders_count']; ?></div>
                            <div class="stat-label">Zamówienia</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php 
                                // Policz uprawnienia
                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_permissions WHERE user_id = ?");
                                $stmt->bind_param("i", $subaccount['id']);
                                $stmt->execute();
                                $perms = $stmt->get_result()->fetch_assoc();
                                echo $perms['count'];
                                ?>
                            </div>
                            <div class="stat-label">Uprawnienia</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php 
                                if ($subaccount['last_login']) {
                                    $days = floor((time() - strtotime($subaccount['last_login'])) / 86400);
                                    echo $days . 'd';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </div>
                            <div class="stat-label">Ostatnie logowanie</div>
                        </div>