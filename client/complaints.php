<?php
// client/complaints.php - Moduł reklamacji dla klienta
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireClient();
checkSessionTimeout();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Pobierz reklamacje użytkownika
$stmt = $conn->prepare("
    SELECT c.*, o.order_number, cd.company_name
    FROM complaints c
    JOIN orders o ON c.order_id = o.id
    JOIN users u ON c.user_id = u.id
    JOIN company_data cd ON u.id = cd.user_id
    WHERE c.user_id = ? OR o.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$complaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pobierz zamówienia użytkownika (do formularza reklamacji)
$stmt = $conn->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
    FROM orders o
    WHERE o.user_id = ? AND o.payment_status = 'Opłacone'
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Zapisz aktywność
logActivity($conn, $userId, 'view_complaints', 'complaints');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Reklamacje - Panel Klienta</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .complaint-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .complaint-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .complaint-number {
            font-size: 18px;
            font-weight: 500;
        }
        
        .complaint-date {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .complaint-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .complaint-status.pending {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .complaint-status.processing {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .complaint-status.accepted {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .complaint-status.completed {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .complaint-details {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 12px;
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-label {
            font-weight: 500;
            min-width: 150px;
        }
        
        .detail-value {
            flex: 1;
        }
        
        .empty-state {
            text-align: center;
            padding: 48px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
        }
        
        /* Dialog stylizacja */
        .mdc-dialog__surface {
            width: 600px;
            max-width: 90vw;
        }
        
        .form-section {
            margin-bottom: 24px;
        }
        
        .form-section-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        
        .order-selection {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px;
        }
        
        .order-option {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: background 0.2s;
            border: 2px solid transparent;
        }
        
        .order-option:hover {
            background: #f5f5f5;
        }
        
        .order-option.selected {
            background: #e3f2fd;
            border-color: var(--primary-color);
        }
        
        .order-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-number {
            font-weight: 500;
        }
        
        .order-meta {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
    </style>
</head>
<body class="mdc-typography">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Reklamacje</h1>
                <?php if (!empty($orders)): ?>
                <button class="mdc-button mdc-button--raised" onclick="showComplaintDialog()">
                    <i class="material-icons mdc-button__icon">add</i>
                    <span class="mdc-button__label">Zgłoś reklamację</span>
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (empty($complaints)): ?>
            <div class="empty-state">
                <i class="material-icons">report_off</i>
                <h3>Brak reklamacji</h3>
                <p>Nie złożyłeś jeszcze żadnej reklamacji.</p>
                <?php if (!empty($orders)): ?>
                <button class="mdc-button mdc-button--raised" onclick="showComplaintDialog()">
                    <span class="mdc-button__label">Zgłoś pierwszą reklamację</span>
                </button>
                <?php else: ?>
                <p style="margin-top: 16px;">Aby zgłosić reklamację, musisz mieć opłacone zamówienie.</p>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="complaints-list">
                <?php foreach ($complaints as $complaint): ?>
                <div class="complaint-card">
                    <div class="complaint-header">
                        <div>
                            <div class="complaint-number">
                                Reklamacja <?php echo escape($complaint['complaint_number']); ?>
                            </div>
                            <div class="complaint-date">
                                Zgłoszona: <?php echo date('d.m.Y H:i', strtotime($complaint['created_at'])); ?>
                            </div>
                        </div>
                        <span class="complaint-status <?php echo getComplaintStatusClass($complaint['status']); ?>">
                            <?php echo escape($complaint['status']); ?>
                        </span>
                    </div>
                    
                    <div class="complaint-details">
                        <div class="detail-row">
                            <span class="detail-label">Zamówienie:</span>
                            <span class="detail-value"><?php echo escape($complaint['order_number']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Przyczyna:</span>
                            <span class="detail-value"><?php echo escape($complaint['reason']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Produkt/Element:</span>
                            <span class="detail-value"><?php echo escape($complaint['product_element']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Opis usterki:</span>
                            <span class="detail-value"><?php echo nl2br(escape($complaint['description'])); ?></span>
                        </div>
                        <?php if ($complaint['updated_at'] != $complaint['created_at']): ?>
                        <div class="detail-row">
                            <span class="detail-label">Ostatnia aktualizacja:</span>
                            <span class="detail-value"><?php echo date('d.m.Y H:i', strtotime($complaint['updated_at'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Dialog zgłaszania reklamacji -->
    <div class="mdc-dialog" id="complaintDialog">
        <div class="mdc-dialog__container">
            <div class="mdc-dialog__surface">
                <h2 class="mdc-dialog__title">Zgłoś reklamację</h2>
                <div class="mdc-dialog__content">
                    <form id="complaintForm">
                        <!-- Wybór zamówienia -->
                        <div class="form-section">
                            <h3 class="form-section-title">1. Wybierz zamówienie do reklamacji</h3>
                            <div class="order-selection">
                                <?php foreach ($orders as $order): ?>
                                <div class="order-option" data-order-id="<?php echo $order['id']; ?>">
                                    <div class="order-info">
                                        <div>
                                            <div class="order-number"><?php echo escape($order['order_number']); ?></div>
                                            <div class="order-meta">
                                                <?php echo date('d.m.Y', strtotime($order['created_at'])); ?> • 
                                                <?php echo $order['items_count']; ?> pozycji • 
                                                <?php echo number_format($order['total_net'], 2, ',', ' '); ?> zł
                                            </div>
                                        </div>
                                        <i class="material-icons">radio_button_unchecked</i>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="order_id" id="selected_order_id" required>
                        </div>
                        
                        <!-- Przyczyna reklamacji -->
                        <div class="form-section">
                            <h3 class="form-section-title">2. Przyczyna reklamacji</h3>
                            <div class="mdc-text-field mdc-text-field--outlined" style="width: 100%;">
                                <input type="text" id="reason" name="reason" class="mdc-text-field__input" required>
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="reason" class="mdc-floating-label">Przyczyna reklamacji</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Produkt/element -->
                        <div class="form-section">
                            <h3 class="form-section-title">3. Jaki produkt/element chcesz zareklamować?</h3>
                            <div class="mdc-text-field mdc-text-field--outlined" style="width: 100%;">
                                <input type="text" id="product_element" name="product_element" class="mdc-text-field__input" required>
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="product_element" class="mdc-floating-label">Produkt/element</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Opis usterki -->
                        <div class="form-section">
                            <h3 class="form-section-title">4. Opis usterki</h3>
                            <div class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea" style="width: 100%;">
                                <textarea id="description" name="description" class="mdc-text-field__input" rows="4" required></textarea>
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="description" class="mdc-floating-label">Szczegółowy opis problemu</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="mdc-dialog__actions">
                    <button type="button" class="mdc-button mdc-dialog__button" data-mdc-dialog-action="cancel">
                        <span class="mdc-button__label">Anuluj</span>
                    </button>
                    <button type="button" class="mdc-button mdc-button--raised mdc-dialog__button" onclick="submitComplaint()">
                        <span class="mdc-button__label">Wyślij reklamację</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="mdc-dialog__scrim"></div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        let complaintDialog;
        
        $(document).ready(function() {
            initializeMDC();
            
            // Inicjalizacja dialogu
            complaintDialog = new mdc.dialog.MDCDialog(document.querySelector('#complaintDialog'));
            
            // Wybór zamówienia
            $('.order-option').on('click', function() {
                $('.order-option').removeClass('selected');
                $('.order-option i').text('radio_button_unchecked');
                
                $(this).addClass('selected');
                $(this).find('i').text('radio_button_checked');
                
                $('#selected_order_id').val($(this).data('order-id'));
            });
        });
        
        function showComplaintDialog() {
            complaintDialog.open();
        }
        
        function submitComplaint() {
            const form = $('#complaintForm');
            
            // Walidacja
            if (!$('#selected_order_id').val()) {
                showNotification('error', 'Wybierz zamówienie do reklamacji');
                return;
            }
            
            if (!form[0].checkValidity()) {
                showNotification('error', 'Wypełnij wszystkie wymagane pola');
                return;
            }
            
            // Wyślij reklamację
            $.ajax({
                url: '../api/complaint_submit.php',
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Reklamacja została zgłoszona');
                        complaintDialog.close();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification('error', response.message || 'Błąd podczas zgłaszania reklamacji');
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

<?php
function getComplaintStatusClass($status) {
    $classes = [
        'Złożona reklamacja' => 'pending',
        'Reklamacja rozpatrywana' => 'processing',
        'Reklamacja przyjęta' => 'accepted',
        'Reklamacja zakończona' => 'completed'
    ];
    return $classes[$status] ?? 'pending';
}
?>