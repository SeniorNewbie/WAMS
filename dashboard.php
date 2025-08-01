<?php
// dashboard.php - Panel główny po zalogowaniu
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'config/session.php';

initSession();
requireLogin();
checkSessionTimeout();

$conn = getDBConnection();
$currentUser = getCurrentUser($conn);

// Pobierz powiadomienia
$stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$unreadNotifications = $result->fetch_assoc()['unread'];

// Pobierz reklamy dla użytkownika
$ads = [];
if (isClient()) {
    $stmt = $conn->prepare("
        SELECT DISTINCT a.* FROM advertisements a
        LEFT JOIN ad_client_groups acg ON a.id = acg.ad_id
        LEFT JOIN ad_clients ac ON a.id = ac.ad_id
        WHERE a.is_active = 1 
        AND a.start_date <= CURDATE() 
        AND a.end_date >= CURDATE()
        AND (acg.client_group = ? OR ac.user_id = ?)
    ");
    $stmt->bind_param("si", $currentUser['client_group'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ads[] = $row;
    }
}

// Zapisz aktywność
logActivity($conn, $_SESSION['user_id'], 'view_dashboard', 'dashboard');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel <?php echo isAdmin() ? 'Administracyjny' : 'Klienta'; ?></title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="mdc-typography">
    <!-- Top App Bar -->
    <header class="mdc-top-app-bar mdc-top-app-bar--fixed">
        <div class="mdc-top-app-bar__row">
            <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start">
                <span class="mdc-top-app-bar__title">
                    Panel <?php echo isAdmin() ? 'Administracyjny' : 'Klienta'; ?>
                </span>
            </section>
            <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-end">
                <!-- Powiadomienia -->
                <button class="mdc-icon-button material-icons mdc-top-app-bar__action-item--unbounded" 
                        id="notifications-button"
                        aria-label="Powiadomienia">
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="notification-badge"><?php echo $unreadNotifications; ?></span>
                    <?php endif; ?>
                    notifications
                </button>
                
                <!-- Menu użytkownika -->
                <button class="mdc-icon-button material-icons mdc-top-app-bar__action-item--unbounded" 
                        id="user-menu-button"
                        aria-label="Menu użytkownika">
                    account_circle
                </button>
                
                <!-- Menu dropdown -->
                <div class="mdc-menu mdc-menu-surface" id="user-menu">
                    <ul class="mdc-list" role="menu">
                        <?php if (isAdmin()): ?>
                            <li class="mdc-list-item" role="menuitem">
                                <span class="mdc-list-item__text">Zarządzaj kontami</span>
                            </li>
                        <?php endif; ?>
                        <li class="mdc-list-item" role="menuitem">
                            <span class="mdc-list-item__text">Edytuj profil</span>
                        </li>
                        <li class="mdc-list-divider" role="separator"></li>
                        <li class="mdc-list-item" role="menuitem" onclick="window.location.href='logout.php'">
                            <span class="mdc-list-item__text">Wyloguj się</span>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
    </header>
    
    <!-- Main content -->
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <!-- Tekst powitalny -->
            <div class="welcome-section">
                <h1 class="welcome-text">
                    Witaj, <?php echo escape($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>!
                </h1>
                <p class="welcome-subtitle">
                    <?php echo escape($currentUser['company_name']); ?>
                </p>
            </div>
            
            <!-- Miejsce na reklamę -->
            <?php if (!empty($ads)): ?>
                <div class="ad-section">
                    <?php foreach ($ads as $ad): ?>
                        <div class="advertisement">
                            <img src="<?php echo escape($ad['image_path']); ?>" 
                                 alt="<?php echo escape($ad['name']); ?>"
                                 class="ad-image">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Moduły -->
            <div class="modules-grid">
                <?php if (isAdmin()): ?>
                    <!-- Moduły administratora -->
                    <div class="module-card mdc-card" onclick="window.location.href='admin/orders.php'">
                        <div class="module-icon">
                            <i class="material-icons">shopping_cart</i>
                        </div>
                        <h3 class="module-title">Zamówienia klientów</h3>
                        <p class="module-description">Zarządzaj zamówieniami wszystkich klientów</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='admin/clients.php'">
                        <div class="module-icon">
                            <i class="material-icons">people</i>
                        </div>
                        <h3 class="module-title">Zarządzanie klientami</h3>
                        <p class="module-description">Dodawaj i edytuj konta klientów</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='admin/complaints.php'">
                        <div class="module-icon">
                            <i class="material-icons">report_problem</i>
                        </div>
                        <h3 class="module-title">Zarządzanie reklamacjami</h3>
                        <p class="module-description">Obsługuj zgłoszenia reklamacyjne</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='admin/ads.php'">
                        <div class="module-icon">
                            <i class="material-icons">campaign</i>
                        </div>
                        <h3 class="module-title">Reklamy</h3>
                        <p class="module-description">Zarządzaj reklamami i promocjami</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='admin/catalogs.php'">
                        <div class="module-icon">
                            <i class="material-icons">folder</i>
                        </div>
                        <h3 class="module-title">Katalogi</h3>
                        <p class="module-description">Zarządzaj katalogami produktów</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='admin/files.php'">
                        <div class="module-icon">
                            <i class="material-icons">cloud_upload</i>
                        </div>
                        <h3 class="module-title">Pliki</h3>
                        <p class="module-description">Dodawaj pliki do dysków klientów</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='admin/invoices.php'">
                        <div class="module-icon">
                            <i class="material-icons">receipt</i>
                        </div>
                        <h3 class="module-title">Faktury/Paragony</h3>
                        <p class="module-description">Zarządzaj dokumentami sprzedaży</p>
                    </div>
                    
                <?php else: ?>
                    <!-- Moduły klienta -->
                    <div class="module-card mdc-card" onclick="window.location.href='client/orders.php'">
                        <div class="module-icon">
                            <i class="material-icons">shopping_cart</i>
                        </div>
                        <h3 class="module-title">Zamówienia</h3>
                        <p class="module-description">Składaj i przeglądaj zamówienia</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='client/catalogs.php'">
                        <div class="module-icon">
                            <i class="material-icons">folder</i>
                        </div>
                        <h3 class="module-title">Katalogi</h3>
                        <p class="module-description">Przeglądaj katalogi produktów</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='client/disk.php'">
                        <div class="module-icon">
                            <i class="material-icons">storage</i>
                        </div>
                        <h3 class="module-title">Dysk klienta</h3>
                        <p class="module-description">Twoje pliki i dokumenty</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='client/password.php'">
                        <div class="module-icon">
                            <i class="material-icons">lock</i>
                        </div>
                        <h3 class="module-title">Zmiana hasła</h3>
                        <p class="module-description">Zmień hasło do konta</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='client/complaints.php'">
                        <div class="module-icon">
                            <i class="material-icons">report_problem</i>
                        </div>
                        <h3 class="module-title">Reklamacje</h3>
                        <p class="module-description">Zgłoś reklamację produktu</p>
                    </div>
                    
                    <div class="module-card mdc-card" onclick="window.location.href='client/subaccounts.php'">
                        <div class="module-icon">
                            <i class="material-icons">group_add</i>
                        </div>
                        <h3 class="module-title">Subkonta</h3>
                        <p class="module-description">Zarządzaj kontami pracowników</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Panel powiadomień -->
    <div class="notifications-panel" id="notifications-panel">
        <div class="notifications-header">
            <h3>Powiadomienia</h3>
            <button class="mdc-icon-button material-icons" id="close-notifications">close</button>
        </div>
        <div class="notifications-content" id="notifications-content">
            <!-- Powiadomienia będą ładowane przez AJAX -->
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Inicjalizacja Material Components
        const topAppBar = new mdc.topAppBar.MDCTopAppBar(document.querySelector('.mdc-top-app-bar'));
        
        // Menu użytkownika
        const menu = new mdc.menu.MDCMenu(document.querySelector('#user-menu'));
        document.querySelector('#user-menu-button').addEventListener('click', () => {
            menu.open = !menu.open;
        });
        
        // Panel powiadomień
        $('#notifications-button').click(function() {
            $('#notifications-panel').addClass('active');
            loadNotifications();
        });
        
        $('#close-notifications').click(function() {
            $('#notifications-panel').removeClass('active');
        });
        
        // Ładowanie powiadomień
        function loadNotifications() {
            $.ajax({
                url: 'api/notifications.php',
                method: 'GET',
                success: function(response) {
                    $('#notifications-content').html(response);
                }
            });
        }
    </script>
</body>
</html>