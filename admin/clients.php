<?php
// admin/clients.php - Zarządzanie klientami
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireAdmin();
checkSessionTimeout();

$conn = getDBConnection();

// Pobierz listę klientów
$page = intval($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = sanitizeInput($_GET['search'] ?? '');

// Buduj zapytanie
$whereClause = "WHERE u.user_type IN ('client', 'subaccount')";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND (cd.company_name LIKE ? OR cd.first_name LIKE ? OR cd.last_name LIKE ? OR u.email LIKE ?)";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
    $types = "ssss";
}

// Pobierz klientów
$sql = "
    SELECT u.*, cd.*, 
           (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as orders_count,
           (SELECT last_login FROM users WHERE id = u.id) as last_login
    FROM users u
    LEFT JOIN company_data cd ON u.id = cd.user_id
    $whereClause
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $params[] = $perPage;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $perPage, $offset);
}
$stmt->execute();
$clients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Policz wszystkich klientów
$countSql = "
    SELECT COUNT(*) as total 
    FROM users u
    LEFT JOIN company_data cd ON u.id = cd.user_id
    $whereClause
";
$stmt = $conn->prepare($countSql);
if (!empty($params)) {
    array_pop($params); // Usuń limit
    array_pop($params); // Usuń offset
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
}
$stmt->execute();
$totalClients = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalClients / $perPage);

// Zapisz aktywność
logActivity($conn, $_SESSION['user_id'], 'view_clients', 'admin_clients');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Zarządzanie klientami - Panel Administracyjny</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .search-bar {
            background: white;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .client-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e0e0e0;
        }
        
        .status-indicator.active {
            background: #4caf50;
        }
        
        .client-info {
            display: flex;
            flex-direction: column;
        }
        
        .client-name {
            font-weight: 500;
        }
        
        .client-company {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .client-actions {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body class="mdc-typography admin">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Zarządzanie klientami</h1>
                <a href="client_add.php" class="mdc-button mdc-button--raised">
                    <i class="material-icons mdc-button__icon">add</i>
                    <span class="mdc-button__label">Dodaj klienta</span>
                </a>
            </div>
            
            <!-- Wyszukiwarka -->
            <div class="search-bar">
                <form method="GET" action="">
                    <div class="mdc-text-field mdc-text-field--outlined mdc-text-field--with-leading-icon">
                        <i class="material-icons mdc-text-field__icon">search</i>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               class="mdc-text-field__input" 
                               value="<?php echo escape($search); ?>"
                               placeholder="Szukaj po nazwie firmy, imieniu, nazwisku lub email">
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="search" class="mdc-floating-label">Wyszukaj klienta</label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (empty($clients)): ?>
            <div class="empty-state">
                <i class="material-icons">people_outline</i>
                <h3>Brak klientów</h3>
                <p>Nie znaleziono klientów spełniających kryteria wyszukiwania.</p>
                <a href="client_add.php" class="mdc-button mdc-button--raised">
                    <span class="mdc-button__label">Dodaj pierwszego klienta</span>
                </a>
            </div>
            <?php else: ?>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Firma / Klient</th>
                            <th>Email</th>
                            <th>NIP</th>
                            <th>Grupa</th>
                            <th>Typ konta</th>
                            <th>Zamówienia</th>
                            <th>Ostatnie logowanie</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td>
                                <div class="client-status">
                                    <span class="status-indicator <?php echo $client['is_active'] ? 'active' : ''; ?>"></span>
                                    <?php echo $client['is_active'] ? 'Aktywny' : 'Nieaktywny'; ?>
                                </div>
                            </td>
                            <td>
                                <div class="client-info">
                                    <span class="client-name">
                                        <?php echo escape($client['first_name'] . ' ' . $client['last_name']); ?>
                                    </span>
                                    <span class="client-company">
                                        <?php echo escape($client['company_name']); ?>
                                    </span>
                                </div>
                            </td>
                            <td><?php echo escape($client['email']); ?></td>
                            <td><?php echo escape($client['nip']); ?></td>
                            <td>
                                <?php if ($client['client_group']): ?>
                                <span class="status-badge">
                                    <?php echo escape($client['client_group']); ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $typeLabel = [
                                    'client' => 'Klient główny',
                                    'subaccount' => 'Subkonto'
                                ];
                                echo $typeLabel[$client['user_type']] ?? '-';
                                ?>
                            </td>
                            <td><?php echo $client['orders_count']; ?></td>
                            <td>
                                <?php 
                                if ($client['last_login']) {
                                    echo date('d.m.Y H:i', strtotime($client['last_login']));
                                } else {
                                    echo '<span class="text-muted">Nigdy</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="client-actions">
                                    <a href="client_edit.php?id=<?php echo $client['id']; ?>" 
                                       class="action-button secondary"
                                       title="Edytuj">
                                        <i class="material-icons">edit</i>
                                    </a>
                                    <a href="client_view.php?id=<?php echo $client['id']; ?>" 
                                       class="action-button secondary"
                                       title="Podgląd">
                                        <i class="material-icons">visibility</i>
                                    </a>
                                    <button onclick="toggleClientStatus(<?php echo $client['id']; ?>, <?php echo $client['is_active'] ? '0' : '1'; ?>)" 
                                            class="action-button secondary"
                                            title="<?php echo $client['is_active'] ? 'Dezaktywuj' : 'Aktywuj'; ?>">
                                        <i class="material-icons">
                                            <?php echo $client['is_active'] ? 'block' : 'check_circle'; ?>
                                        </i>
                                    </button>
                                    <button onclick="loginAsClient(<?php echo $client['id']; ?>)" 
                                            class="action-button primary"
                                            title="Zaloguj jako klient">
                                        <i class="material-icons">login</i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                   class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Automatyczne wysyłanie formularza wyszukiwania
        let searchTimeout;
        $('#search').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                $('form').submit();
            }, 500);
        });
        
        // Przełączanie statusu klienta
        function toggleClientStatus(clientId, newStatus) {
            const action = newStatus ? 'aktywować' : 'dezaktywować';
            
            if (!confirm(`Czy na pewno chcesz ${action} tego klienta?`)) {
                return;
            }
            
            $.ajax({
                url: '../api/admin/toggle_client_status.php',
                method: 'POST',
                data: {
                    client_id: clientId,
                    is_active: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification('error', response.message || 'Wystąpił błąd');
                    }
                },
                error: function() {
                    showNotification('error', 'Błąd połączenia z serwerem');
                }
            });
        }
        
        // Logowanie jako klient
        function loginAsClient(clientId) {
            if (!confirm('Czy na pewno chcesz zalogować się jako ten klient?')) {
                return;
            }
            
            $.ajax({
                url: '../api/admin/login_as_client.php',
                method: 'POST',
                data: { client_id: clientId },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '../dashboard.php';
                    } else {
                        showNotification('error', response.message || 'Nie można zalogować jako klient');
                    }
                },
                error: function() {
                    showNotification('error', 'Błąd połączenia z serwerem');
                }
            });
        }
    </script>
</body>
</html>