<?php
// admin/invoices.php - Zarządzanie fakturami
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireAdmin();
checkSessionTimeout();

$conn = getDBConnection();

// Pobierz klientów z zamówieniami
$clients = fetchAll($conn, "
    SELECT DISTINCT u.id, cd.company_name, cd.first_name, cd.last_name,
           (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as orders_count,
           (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND invoice_path IS NOT NULL) as invoiced_count
    FROM users u
    JOIN company_data cd ON u.id = cd.user_id
    JOIN orders o ON u.id = o.user_id
    WHERE u.user_type IN ('client', 'subaccount')
    ORDER BY cd.company_name
");

// Zapisz aktywność
logActivity($conn, $_SESSION['user_id'], 'view_invoices_admin', 'admin_invoices');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Zarządzanie fakturami - Panel Administracyjny</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .clients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }
        
        .client-invoice-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .client-invoice-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .client-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .client-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .client-icon i {
            font-size: 24px;
            color: #388e3c;
        }
        
        .client-details {
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
        
        .invoice-stats {
            display: flex;
            justify-content: space-around;
            padding-top: 16px;
            border-top: 1px solid #f5f5f5;
        }
        
        .invoice-stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 500;
            color: var(--admin-primary);
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        
        /* Panel zamówień */
        .orders-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: 700px;
            height: 100%;
            background: white;
            box-shadow: -2px 0 8px rgba(0,0,0,0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1100;
            display: flex;
            flex-direction: column;
        }
        
        .orders-panel.active {
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
        
        .order-item {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .order-number {
            font-weight: 500;
        }
        
        .order-date {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .order-detail {
            display: flex;
            justify-content: space-between;
        }
        
        .detail-label {
            color: var(--text-secondary);
        }
        
        .invoice-section {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
            margin-top: 12px;
        }
        
        .invoice-exists {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #388e3c;
        }
        
        .upload-area {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: var(--admin-primary);
            background: #fafafa;
        }
        
        .upload-area.drag-over {
            border-color: var(--admin-primary);
            background: #ffebee;
        }
        
        .payment-timer {
            margin-top: 8px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .payment-timer.overdue {
            color: var(--error-color);
            font-weight: 500;
        }
    </style>
</head>
<body class="mdc-typography admin">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Zarządzanie fakturami</h1>
            </div>
            
            <!-- Lista klientów -->
            <div class="clients-grid">
                <?php foreach ($clients as $client): ?>
                <div class="client-invoice-card" onclick="showClientOrders(<?php echo $client['id']; ?>)">
                    <div class="client-header">
                        <div class="client-icon">
                            <i class="material-icons">receipt</i>
                        </div>
                        <div class="client-details">
                            <div class="client-name">
                                <?php echo escape($client['first_name'] . ' ' . $client['last_name']); ?>
                            </div>
                            <div class="client-company">
                                <?php echo escape($client['company_name']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="invoice-stats">
                        <div class="invoice-stat">
                            <div class="stat-value"><?php echo $client['orders_count']; ?></div>
                            <div class="stat-label">Zamówienia</div>
                        </div>
                        <div class="invoice-stat">
                            <div class="stat-value"><?php echo $client['invoiced_count']; ?></div>
                            <div class="stat-label">Faktury</div>
                        </div>
                        <div class="invoice-stat">
                            <div class="stat-value">
                                <?php echo $client['orders_count'] - $client['invoiced_count']; ?>
                            </div>
                            <div class="stat-label">Bez faktury</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    
    <!-- Panel zamówień klienta -->
    <div class="orders-panel" id="ordersPanel">
        <div class="panel-header">
            <div>
                <h2 id="panelClientName">Zamówienia klienta</h2>
                <p id="panelClientCompany" style="margin: 0; color: var(--text-secondary);"></p>
            </div>
            <button class="mdc-icon-button material-icons" onclick="closeOrdersPanel()">close</button>
        </div>
        <div class="panel-content" id="ordersContent">
            <!-- Zawartość będzie ładowana dynamicznie -->
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        let currentClientId = null;
        let currentOrderId = null;
        
        function showClientOrders(clientId) {
            currentClientId = clientId;
            
            // Pobierz informacje o kliencie i jego zamówienia
            $.ajax({
                url: '../api/admin/get_client_orders.php',
                method: 'GET',
                data: { client_id: clientId },
                success: function(response) {
                    if (response.success) {
                        $('#panelClientName').text('Zamówienia - ' + response.client.name);
                        $('#panelClientCompany').text(response.client.company);
                        
                        displayOrders(response.orders);
                        $('#ordersPanel').addClass('active');
                    }
                }
            });
        }
        
        function closeOrdersPanel() {
            $('#ordersPanel').removeClass('active');
            currentClientId = null;
        }
        
        function displayOrders(orders) {
            const container = $('#ordersContent');
            container.empty();
            
            if (orders.length === 0) {
                container.html('<p class="text-center text-muted">Brak zamówień</p>');
                return;
            }
            
            orders.forEach(order => {
                const hasInvoice = order.invoice_path !== null;
                const paymentDays = calculatePaymentDays(order);
                
                const orderHtml = `
                    <div class="order-item">
                        <div class="order-header">
                            <div>
                                <div class="order-number">${order.order_number}</div>
                                <div class="order-date">${formatDate(order.created_at)}</div>
                            </div>
                            <span class="status-badge ${getPaymentStatusClass(order.payment_status)}">
                                ${order.payment_status}
                            </span>
                        </div>
                        
                        <div class="order-details">
                            <div class="order-detail">
                                <span class="detail-label">Wartość netto:</span>
                                <span>${formatPrice(order.total_net)}</span>
                            </div>
                            <div class="order-detail">
                                <span class="detail-label">Status realizacji:</span>
                                <span>${order.order_status}</span>
                            </div>
                        </div>
                        
                        <div class="invoice-section">
                            ${hasInvoice ? `
                                <div class="invoice-exists">
                                    <i class="material-icons">check_circle</i>
                                    <span>Faktura została dodana</span>
                                    <button class="action-button secondary" onclick="downloadInvoice(${order.id})">
                                        <i class="material-icons">download</i>
                                        Pobierz
                                    </button>
                                    <button class="action-button secondary" onclick="replaceInvoice(${order.id})">
                                        <i class="material-icons">swap_horiz</i>
                                        Zamień
                                    </button>
                                </div>
                                ${order.payment_status !== 'Opłacone' ? `
                                    <div class="payment-timer ${paymentDays < 0 ? 'overdue' : ''}">
                                        ${paymentDays < 0 ? 
                                            `Termin płatności minął ${Math.abs(paymentDays)} dni temu` : 
                                            `Pozostało ${paymentDays} dni do terminu płatności`
                                        }
                                    </div>
                                ` : ''}
                            ` : `
                                <div class="upload-area" onclick="selectInvoiceFile(${order.id})">
                                    <i class="material-icons" style="font-size: 48px; color: #9e9e9e;">upload_file</i>
                                    <p>Kliknij aby dodać fakturę</p>
                                    <input type="file" id="invoice_${order.id}" accept=".pdf" style="display: none;" 
                                           onchange="uploadInvoice(${order.id}, this)">
                                </div>
                            `}
                        </div>
                    </div>
                `;
                
                container.append(orderHtml);
            });
        }
        
        function selectInvoiceFile(orderId) {
            currentOrderId = orderId;
            $(`#invoice_${orderId}`).click();
        }
        
        function uploadInvoice(orderId, input) {
            if (!input.files || !input.files[0]) return;
            
            const file = input.files[0];
            if (file.type !== 'application/pdf') {
                showNotification('error', 'Tylko pliki PDF są dozwolone');
                return;
            }
            
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('invoice', file);
            
            $.ajax({
                url: '../api/admin/upload_invoice.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    showNotification('info', 'Przesyłanie faktury...');
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Faktura została dodana');
                        
                        // Odśwież listę zamówień
                        showClientOrders(currentClientId);
                        
                        // Wyślij powiadomienie do klienta
                        sendInvoiceNotification(orderId);
                    } else {
                        showNotification('error', response.message || 'Błąd przesyłania faktury');
                    }
                },
                error: function() {
                    showNotification('error', 'Błąd połączenia z serwerem');
                }
            });
        }
        
        function replaceInvoice(orderId) {
            if (confirm('Czy na pewno chcesz zamienić istniejącą fakturę?')) {
                selectInvoiceFile(orderId);
            }
        }
        
        function downloadInvoice(orderId) {
            window.location.href = '../api/download.php?type=invoice&order_id=' + orderId;
        }
        
        function sendInvoiceNotification(orderId) {
            $.ajax({
                url: '../api/admin/send_invoice_notification.php',
                method: 'POST',
                data: { order_id: orderId },
                success: function(response) {
                    if (response.success) {
                        console.log('Powiadomienie wysłane');
                    }
                }
            });
        }
        
        function calculatePaymentDays(order) {
            if (!order.invoice_path || order.payment_status === 'Opłacone') {
                return null;
            }
            
            // Zakładamy 14 dni na płatność od daty wystawienia faktury
            const invoiceDate = new Date(order.invoice_uploaded_at || order.created_at);
            const dueDate = new Date(invoiceDate);
            dueDate.setDate(dueDate.getDate() + 14);
            
            const today = new Date();
            const diffTime = dueDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            return diffDays;
        }
        
        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('pl-PL');
        }
        
        function formatPrice(price) {
            return parseFloat(price).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' zł';
        }
        
        function getPaymentStatusClass(status) {
            const classes = {
                'Opłacone': 'paid',
                'Nieopłacone': 'unpaid',
                'Częściowo opłacone': 'partial'
            };
            return classes[status] || '';
        }
        
        // Drag & Drop
        $(document).on('dragover', '.upload-area', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '.upload-area', function() {
            $(this).removeClass('drag-over');
        });
        
        $(document).on('drop', '.upload-area', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            
            const orderId = $(this).attr('onclick').match(/\d+/)[0];
            const files = e.originalEvent.dataTransfer.files;
            
            if (files.length > 0) {
                const input = $(`#invoice_${orderId}`)[0];
                input.files = files;
                uploadInvoice(orderId, input);
            }
        });
    </script>
</body>
</html>