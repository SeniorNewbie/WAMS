<?php
// client/orders.php - Moduł zamówień dla klienta
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireClient();
checkSessionTimeout();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Sprawdź uprawnienia do modułu
$stmt = $conn->prepare("
    SELECT COUNT(*) as has_access 
    FROM order_permissions 
    WHERE user_id = ? AND (can_quote = 1 OR can_order = 1)
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$hasAccess = $result->fetch_assoc()['has_access'] > 0;

if (!$hasAccess) {
    header('Location: ../dashboard.php');
    exit();
}

// Pobierz uprawnienia użytkownika
$stmt = $conn->prepare("SELECT * FROM order_permissions WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$permissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$canOrder = false;
$canQuote = false;
foreach ($permissions as $perm) {
    if ($perm['can_order']) $canOrder = true;
    if ($perm['can_quote']) $canQuote = true;
}

// Pobierz zamówienia użytkownika
$page = intval($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare("
    SELECT o.*, cd.company_name, cd.first_name, cd.last_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN company_data cd ON u.id = cd.user_id
    WHERE o.user_id = ? OR u.parent_id = ?
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iiii", $userId, $userId, $perPage, $offset);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pobierz liczbę wszystkich zamówień
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.user_id = ? OR u.parent_id = ?
");
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $perPage);

// Zapisz aktywność
logActivity($conn, $userId, 'view_orders', 'orders');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Zamówienia - Panel Klienta</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="mdc-typography">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Zamówienia</h1>
                <?php if ($canOrder): ?>
                <a href="order_add.php" class="mdc-button mdc-button--raised">
                    <i class="material-icons mdc-button__icon">add</i>
                    <span class="mdc-button__label">Dodaj zamówienie</span>
                </a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="material-icons">shopping_cart</i>
                <h3>Brak zamówień</h3>
                <p>Nie masz jeszcze żadnych zamówień.</p>
                <?php if ($canOrder): ?>
                <a href="order_add.php" class="mdc-button mdc-button--raised">
                    <span class="mdc-button__label">Złóż pierwsze zamówienie</span>
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nr. Zamówienia</th>
                            <th>Nazwa firmy</th>
                            <th>Imię i nazwisko</th>
                            <th>Status płatności</th>
                            <th>Status realizacji</th>
                            <th>Data złożenia</th>
                            <th>Wartość netto</th>
                            <th>Faktura</th>
                            <th>Wycena</th>
                            <th>Akcja</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo escape($order['order_number']); ?></td>
                            <td><?php echo escape($order['company_name']); ?></td>
                            <td><?php echo escape($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch ($order['payment_status']) {
                                    case 'Opłacone':
                                        $statusClass = 'paid';
                                        break;
                                    case 'Nieopłacone':
                                        $statusClass = 'unpaid';
                                        break;
                                    case 'Częściowo opłacone':
                                        $statusClass = 'partial';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo escape($order['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch ($order['order_status']) {
                                    case 'Zamówienie czeka na potwierdzenie':
                                    case 'Zamówienie przyjęte':
                                    case 'Zamówienie czeka na realizację':
                                        $statusClass = 'pending';
                                        break;
                                    case 'Zamówienie jest w trakcie realizacji':
                                        $statusClass = 'processing';
                                        break;
                                    case 'Zamówienie jest gotowe do odbioru':
                                        $statusClass = 'ready';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo escape($order['order_status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><?php echo number_format($order['total_net'], 2, ',', ' '); ?> zł</td>
                            <td>
                                <?php if (!empty($order['invoice_path'])): ?>
                                <button class="action-button primary" onclick="downloadInvoice(<?php echo $order['id']; ?>)">
                                    <i class="material-icons">download</i>
                                    Pobierz
                                </button>
                                <?php else: ?>
                                <button class="action-button secondary" disabled>
                                    <i class="material-icons">receipt</i>
                                    Brak
                                </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="action-button secondary" onclick="generateQuote(<?php echo $order['id']; ?>)">
                                    <i class="material-icons">picture_as_pdf</i>
                                    Generuj
                                </button>
                            </td>
                            <td>
                                <a href="order_view.php?id=<?php echo $order['id']; ?>" class="action-button secondary">
                                    <i class="material-icons">visibility</i>
                                    Podgląd
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>">
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
        function downloadInvoice(orderId) {
            window.location.href = '../api/download.php?type=invoice&order_id=' + orderId;
        }
        
        function generateQuote(orderId) {
            $.ajax({
                url: '../api/generate_quote.php',
                method: 'POST',
                data: { order_id: orderId },
                beforeSend: function() {
                    showNotification('info', 'Generowanie wyceny...');
                },
                success: function(response) {
                    if (response.success) {
                        window.open(response.url, '_blank');
                        showNotification('success', 'Wycena została wygenerowana');
                    } else {
                        showNotification('error', response.message || 'Błąd generowania wyceny');
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