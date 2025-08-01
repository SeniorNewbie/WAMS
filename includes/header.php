<?php
// includes/header.php - Wspólny nagłówek dla wszystkich stron

// Sprawdź czy sesja jest zainicjowana
if (session_status() === PHP_SESSION_NONE) {
    require_once dirname(__DIR__) . '/config/session.php';
    initSession();
}

// Pobierz dane użytkownika jeśli zalogowany
$headerUser = null;
$unreadNotifications = 0;

if (isLoggedIn()) {
    $conn = getDBConnection();
    $headerUser = getCurrentUser($conn);
    
    // Liczba nieprzeczytanych powiadomień
    $stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $unreadNotifications = $result->fetch_assoc()['unread'];
}

// Określ typ panelu
$panelType = isAdmin() ? 'Administracyjny' : 'Klienta';
$bodyClass = isAdmin() ? 'admin' : 'client';
?>

<body class="mdc-typography <?php echo $bodyClass; ?>">
    <!-- Top App Bar -->
    <header class="mdc-top-app-bar mdc-top-app-bar--fixed">
        <div class="mdc-top-app-bar__row">
            <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start">
                <button class="material-icons mdc-top-app-bar__navigation-icon mdc-icon-button" id="menu-button">menu</button>
                <span class="mdc-top-app-bar__title">
                    Panel <?php echo $panelType; ?>
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
                            <li class="mdc-list-item" role="menuitem" onclick="window.location.href='/admin/clients.php'">
                                <i class="material-icons mdc-list-item__graphic">people</i>
                                <span class="mdc-list-item__text">Zarządzaj kontami</span>
                            </li>
                        <?php endif; ?>
                        <li class="mdc-list-item" role="menuitem" onclick="window.location.href='/profile.php'">
                            <i class="material-icons mdc-list-item__graphic">person</i>
                            <span class="mdc-list-item__text">Edytuj profil</span>
                        </li>
                        <?php if (isClient()): ?>
                            <li class="mdc-list-item" role="menuitem" onclick="window.location.href='/client/password.php'">
                                <i class="material-icons mdc-list-item__graphic">lock</i>
                                <span class="mdc-list-item__text">Zmień hasło</span>
                            </li>
                        <?php endif; ?>
                        <li class="mdc-list-divider" role="separator"></li>
                        <li class="mdc-list-item" role="menuitem" onclick="window.location.href='/logout.php'">
                            <i class="material-icons mdc-list-item__graphic">exit_to_app</i>
                            <span class="mdc-list-item__text">Wyloguj się</span>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
    </header>
    
    <!-- Navigation Drawer -->
    <aside class="mdc-drawer mdc-drawer--modal" id="navigation-drawer">
        <div class="mdc-drawer__header">
            <h3 class="mdc-drawer__title">
                <?php echo escape($headerUser['first_name'] . ' ' . $headerUser['last_name']); ?>
            </h3>
            <h6 class="mdc-drawer__subtitle">
                <?php echo escape($headerUser['company_name']); ?>
            </h6>
        </div>
        <div class="mdc-drawer__content">
            <nav class="mdc-list">
                <a class="mdc-list-item" href="/dashboard.php">
                    <i class="material-icons mdc-list-item__graphic">dashboard</i>
                    <span class="mdc-list-item__text">Panel główny</span>
                </a>
                
                <?php if (isAdmin()): ?>
                    <!-- Menu administratora -->
                    <hr class="mdc-list-divider">
                    <h6 class="mdc-list-group__subheader">Zarządzanie</h6>
                    
                    <a class="mdc-list-item" href="/admin/orders.php">
                        <i class="material-icons mdc-list-item__graphic">shopping_cart</i>
                        <span class="mdc-list-item__text">Zamówienia klientów</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/admin/clients.php">
                        <i class="material-icons mdc-list-item__graphic">people</i>
                        <span class="mdc-list-item__text">Klienci</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/admin/complaints.php">
                        <i class="material-icons mdc-list-item__graphic">report_problem</i>
                        <span class="mdc-list-item__text">Reklamacje</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/admin/ads.php">
                        <i class="material-icons mdc-list-item__graphic">campaign</i>
                        <span class="mdc-list-item__text">Reklamy</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/admin/catalogs.php">
                        <i class="material-icons mdc-list-item__graphic">folder</i>
                        <span class="mdc-list-item__text">Katalogi</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/admin/files.php">
                        <i class="material-icons mdc-list-item__graphic">cloud_upload</i>
                        <span class="mdc-list-item__text">Pliki klientów</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/admin/invoices.php">
                        <i class="material-icons mdc-list-item__graphic">receipt</i>
                        <span class="mdc-list-item__text">Faktury</span>
                    </a>
                    
                <?php else: ?>
                    <!-- Menu klienta -->
                    <hr class="mdc-list-divider">
                    <h6 class="mdc-list-group__subheader">Moduły</h6>
                    
                    <a class="mdc-list-item" href="/client/orders.php">
                        <i class="material-icons mdc-list-item__graphic">shopping_cart</i>
                        <span class="mdc-list-item__text">Zamówienia</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/client/catalogs.php">
                        <i class="material-icons mdc-list-item__graphic">folder</i>
                        <span class="mdc-list-item__text">Katalogi</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/client/disk.php">
                        <i class="material-icons mdc-list-item__graphic">storage</i>
                        <span class="mdc-list-item__text">Dysk klienta</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/client/complaints.php">
                        <i class="material-icons mdc-list-item__graphic">report_problem</i>
                        <span class="mdc-list-item__text">Reklamacje</span>
                    </a>
                    
                    <a class="mdc-list-item" href="/client/subaccounts.php">
                        <i class="material-icons mdc-list-item__graphic">group_add</i>
                        <span class="mdc-list-item__text">Subkonta</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </aside>
    
    <div class="mdc-drawer-scrim"></div>
    
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
    
    <script>
        // Inicjalizacja drawer
        const drawer = new mdc.drawer.MDCDrawer(document.querySelector('.mdc-drawer'));
        document.querySelector('#menu-button').addEventListener('click', () => {
            drawer.open = !drawer.open;
        });
        
        // Podświetl aktywną stronę
        const currentPath = window.location.pathname;
        document.querySelectorAll('.mdc-list-item').forEach(item => {
            if (item.getAttribute('href') === currentPath) {
                item.classList.add('mdc-list-item--activated');
            }
        });
    </script>