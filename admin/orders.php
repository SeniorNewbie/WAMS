<?php
// admin/orders.php - Zarządzanie zamówieniami klientów
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireAdmin();
checkSessionTimeout();

$conn = getDBConnection();

// Filtry
$statusFilter = $_GET['status'] ?? '';
$paymentFilter = $_GET['payment'] ?? '';
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// Buduj zapytanie
$whereClause = "WHERE o.created_at BETWEEN ? AND ?";
$params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
$types = "ss";

if (!empty($statusFilter)) {
    $whereClause .= " AND o.order_status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($paymentFilter)) {
    $whereClause .= " AND o.payment_status = ?";
    $params[] = $paymentFilter;
    $types .= "s";
}

// Pobierz zamówienia
$sql = "
    SELECT o.*, cd.company_name, cd.first_name, cd.last_name, u.email,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN company_data cd ON u.id = cd.user_id
    $whereClause
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Statystyki
$stats = [
    'total_orders' => count($orders),
    'total_value' => array_sum(array_column($orders, 'total_net')),
    'paid' => 0,
    'unpaid' => 0,
    'pending' => 0,
    'completed' => 0
];

foreach ($orders as $order) {
    if ($order['payment_status'] === 'Opłacone') $stats['paid']++;
    if ($order['payment_status'] === 'Nieopłacone') $stats['unpaid']++;
    if (in_array($order['order_status'], ['Zamówienie czeka na potwierdzenie', 'Zamówienie przyjęte', 'Zamówienie czeka na realizację'])) {
        $stats['pending']++;
    }
    if ($order['order_status'] === 'Zamówienie jest gotowe do odbioru') $stats['completed']++;
}

// Zapisz aktywność
logActivity($conn, $_SESSION['user_id'], 'view_orders_admin', 'admin_orders');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Zarządzanie zamówieniami - Panel Administracyjny</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 500;
            color: var(--admin-primary);
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .filters-section {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .order-row {
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .order-row:hover {
            background: #fafafa;
        }
        
        /* Panel szczegółów */
        .order-details-panel {
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
        
        .order-details-panel.active {
            transform: translateX(0);
        }
        
        .panel-header {
            padding: 24px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .panel-content {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }
        
        .detail-section {
            margin-bottom: 32px;
        }
        
        .detail-section h3 {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        
        .status-selects {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .order-items-table {
            width: 100%;
            font-size: 14px;
        }
        
        .order-items-table th {
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .order-items-table td {
            padding: 8px;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 16px;
            padding: 16px;
            background: #f5f5f5;
            border-radius: 8px;
        }
    </style>
</head>
<body class="mdc-typography admin">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Zamówienia klientów</h1>
            </div>
            
            <!-- Statystyki -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                    <div class="stat-label">Wszystkie zamówienia</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['total_value'], 2, ',', ' '); ?> zł</div>
                    <div class="stat-label">Wartość zamówień</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['paid']; ?></div>
                    <div class="stat-label">Opłacone</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">W realizacji</div>
                </div>
            </div>
            
            <!-- Filtry -->
            <div class="filters-section">
                <form method="GET" class="filters-grid">
                    <div class="mdc-text-field mdc-text-field--outlined">
                        <input type="date" id="date_from" name="date_from" class="mdc-text-field__input" 
                               value="<?php echo $dateFrom; ?>">
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="date_from" class="mdc-floating-label">Data od</label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                    
                    <div class="mdc-text-field mdc-text-field--outlined">
                        <input type="date" id="date_to" name="date_to" class="mdc-text-field__input" 
                               value="<?php echo $dateTo; ?>">
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="date_to" class="mdc-floating-label">Data do</label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                    
                    <select name="status" class="mdc-select__native-control">
                        <option value="">-- Status realizacji --</option>
                        <option value="Zamówienie czeka na potwierdzenie" <?php echo $statusFilter === 'Zamówienie czeka na potwierdzenie' ? 'selected' : ''; ?>>
                            Czeka na potwierdzenie
                        </option>
                        <option value="Zamówienie przyjęte" <?php echo $statusFilter === 'Zamówienie przyjęte' ? 'selected' : ''; ?>>
                            Przyjęte
                        </option>
                        <option value="Zamówienie czeka na realizację" <?php echo $statusFilter === 'Zamówienie czeka na realizację' ? 'selected' : ''; ?>>
                            Czeka na realizację
                        </option>
                        <option value="Zamówienie jest w trakcie realizacji" <?php echo $statusFilter === 'Zamówienie jest w trakcie realizacji' ? 'selected' : ''; ?>>
                            W trakcie realizacji
                        </option>
                        <option value="Zamówienie jest gotowe do odbioru" <?php echo $statusFilter === 'Zamówienie jest gotowe do odbioru' ? 'selected' : ''; ?>>
                            Gotowe do odbioru
                        </option>
                    </select>
                    
                    <select name="payment" class="mdc-select__native-control">
                        <option value="">-- Status płatności --</option>
                        <option value="Nieopłacone" <?php echo $paymentFilter === 'Nieopłacone' ? 'selected' : ''; ?>>Nieopłacone</option>
                        <option value="Opłacone" <?php echo $paymentFilter === 'Opłacone' ? 'selected' : ''; ?>>Opłacone</option>
                        <option value="Częściowo opłacone" <?php echo $paymentFilter === 'Częściowo opłacone' ? 'selected' : ''; ?>>Częściowo opłacone</option>
                    </select>
                    
                    <button type="submit" class="mdc-button mdc-button--raised">
                        <i class="material-icons mdc-button__icon">filter_list</i>
                        <span class="mdc-button__label">Filtruj</span>
                    </button>
                </form>
            </div>
            
            <!-- Tabela zamówień -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nr zamówienia</th>
                            <th>Firma</th>
                            <th>Klient</th>
                            <th>Data złożenia</th>
                            <th>Wartość netto</th>
                            <th>Status płatności</th>
                            <th>Status realizacji</th>
                            <th>Faktura</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr class="order-row" onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                            <td><?php echo escape($order['order_number']); ?></td>
                            <td><?php echo escape($order['company_name']); ?></td>
                            <td><?php echo escape($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><?php echo number_format($order['total_net'], 2, ',', ' '); ?> zł</td>
                            <td>
                                <span class="status-badge <?php echo getPaymentStatusClass($order['payment_status']); ?>">
                                    <?php echo escape($order['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo getOrderStatusClass($order['order_status']); ?>">
                                    <?php echo escape($order['order_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($order['invoice_path']): ?>
                                <i class="material-icons" style="color: #4caf50;">check_circle</i>
                                <?php else: ?>
                                <i class="material-icons" style="color: #f44336;">cancel</i>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <!-- Panel szczegółów zamówienia -->
    <div class="order-details-panel" id="orderDetailsPanel">
        <div class="panel-header">
            <h2 id="orderPanelTitle">Szczegóły zamówienia</h2>
            <button class="mdc-icon-button material-icons" onclick="closeDetailsPanel()">close</button>
        </div>
        <div class="panel-content" id="orderDetailsContent">
            <!-- Zawartość będzie ładowana dynamicznie -->
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        let ordersData = <?php echo json_encode($orders); ?>;
        let currentOrderId = null;
        
        function showOrderDetails(orderId) {
            currentOrderId = orderId;
            
            $.ajax({
                url: '../api/admin/get_order_details.php',
                method: 'GET',
                data: { order_id: orderId },
                success: function(response) {
                    if (response.success) {
                        displayOrderDetails(response.order, response.items);
                        $('#orderDetailsPanel').addClass('active');
                    }
                }
            });
        }
        
        function closeDetailsPanel() {
            $('#orderDetailsPanel').removeClass('active');
            currentOrderId = null;
        }
        
        function displayOrderDetails(order, items) {
            $('#orderPanelTitle').text(`Zamówienie ${order.order_number}`);
            
            let itemsHtml = '';
            items.forEach((item, index) => {
                itemsHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.product}</td>
                        <td>${item.width} x ${item.height}</td>
                        <td>${item.quantity}</td>
                        <td>${formatPrice(item.price_net)}</td>
                    </tr>
                `;
            });
            
            const content = `
                <div class="detail-section">
                    <h3>Informacje o kliencie</h3>
                    <p><strong>Firma:</strong> ${order.company_name}</p>
                    <p><strong>Osoba:</strong> ${order.first_name} ${order.last_name}</p>
                    <p><strong>Email:</strong> ${order.email}</p>
                </div>
                
                <div class="detail-section">
                    <h3>Status zamówienia</h3>
                    <div class="status-selects">
                        <div>
                            <label>Status płatności</label>
                            <select id="paymentStatus" class="mdc-select__native-control">
                                <option value="Nieopłacone" ${order.payment_status === 'Nieopłacone' ? 'selected' : ''}>Nieopłacone</option>
                                <option value="Opłacone" ${order.payment_status === 'Opłacone' ? 'selected' : ''}>Opłacone</option>
                                <option value="Częściowo opłacone" ${order.payment_status === 'Częściowo opłacone' ? 'selected' : ''}>Częściowo opłacone</option>
                            </select>
                        </div>
                        <div>
                            <label>Status realizacji</label>
                            <select id="orderStatus" class="mdc-select__native-control">
                                <option value="Zamówienie czeka na potwierdzenie" ${order.order_status === 'Zamówienie czeka na potwierdzenie' ? 'selected' : ''}>
                                    Czeka na potwierdzenie
                                </option>
                                <option value="Zamówienie przyjęte" ${order.order_status === 'Zamówienie przyjęte' ? 'selected' : ''}>
                                    Przyjęte
                                </option>
                                <option value="Zamówienie czeka na realizację" ${order.order_status === 'Zamówienie czeka na realizację' ? 'selected' : ''}>
                                    Czeka na realizację
                                </option>
                                <option value="Zamówienie jest w trakcie realizacji" ${order.order_status === 'Zamówienie jest w trakcie realizacji' ? 'selected' : ''}>
                                    W trakcie realizacji
                                </option>
                                <option value="Zamówienie jest gotowe do odbioru" ${order.order_status === 'Zamówienie jest gotowe do odbioru' ? 'selected' : ''}>
                                    Gotowe do odbioru
                                </option>
                            </select>
                        </div>
                    </div>
                    <button class="mdc-button mdc-button--raised" onclick="updateOrderStatus()" style="width: 100%; margin-top: 16px;">
                        <span class="mdc-button__label">Zapisz zmiany</span>
                    </button>
                </div>
                
                <div class="detail-section">
                    <h3>Pozycje zamówienia</h3>
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Nr</th>
                                <th>Produkt</th>
                                <th>Wymiary</th>
                                <th>Ilość</th>
                                <th>Cena netto</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                    
                    <div class="summary-grid">
                        <div>
                            <strong>Liczba pozycji:</strong> ${items.length}
                        </div>
                        <div>
                            <strong>Wartość całkowita:</strong> ${formatPrice(order.total_net)}
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Akcje</h3>
                    <button class="mdc-button mdc-button--outlined" onclick="generateQuote(${order.id})" style="width: 100%; margin-bottom: 8px;">
                        <i class="material-icons mdc-button__icon">picture_as_pdf</i>
                        <span class="mdc-button__label">Generuj wycenę PDF</span>
                    </button>
                    ${order.invoice_path ? `
                        <button class="mdc-button mdc-button--outlined" onclick="downloadInvoice(${order.id})" style="width: 100%; margin-bottom: 8px;">
                            <i class="material-icons mdc-button__icon">download</i>
                            <span class="mdc-button__label">Pobierz fakturę</span>
                        </button>
                    ` : ''}
                    <button class="mdc-button mdc-button--outlined" onclick="printOrder(${order.id})" style="width: 100%;">
                        <i class="material-icons mdc-button__icon">print</i>
                        <span class="mdc-button__label">Drukuj zamówienie</span>
                    </button>
                </div>
            `;
            
            $('#orderDetailsContent').html(content);
        }
        
        function updateOrderStatus() {
            const paymentStatus = $('#paymentStatus').val();
            const orderStatus = $('#orderStatus').val();
            
            $.ajax({
                url: '../api/admin/update_order_status.php',
                method: 'POST',
                data: {
                    order_id: currentOrderId,
                    payment_status: paymentStatus,
                    order_status: orderStatus
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Status został zaktualizowany');
                        
                        // Odśwież tabelę
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('error', response.message || 'Błąd aktualizacji');
                    }
                }
            });
        }
        
        function generateQuote(orderId) {
            window.open('../api/generate_quote.php?order_id=' + orderId, '_blank');
        }
        
        function downloadInvoice(orderId) {
            window.location.href = '../api/download.php?type=invoice&order_id=' + orderId;
        }
        
        function printOrder(orderId) {
            window.open('../api/print_order.php?order_id=' + orderId, '_blank');
        }
        
        function formatPrice(price) {
            return parseFloat(price).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' zł';
        }
    </script>
</body>
</html>

<?php
function getPaymentStatusClass($status) {
    $classes = [
        'Opłacone' => 'paid',
        'Nieopłacone' => 'unpaid',
        'Częściowo opłacone' => 'partial'
    ];
    return $classes[$status] ?? '';
}

function getOrderStatusClass($status) {
    $classes = [
        'Zamówienie czeka na potwierdzenie' => 'pending',
        'Zamówienie przyjęte' => 'pending',
        'Zamówienie czeka na realizację' => 'pending',
        'Zamówienie jest w trakcie realizacji' => 'processing',
        'Zamówienie jest gotowe do odbioru' => 'ready'
    ];
    return $classes[$status] ?? '';
}
?>