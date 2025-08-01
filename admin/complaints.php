<?php
// admin/complaints.php - Zarządzanie reklamacjami
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireAdmin();
checkSessionTimeout();

$conn = getDBConnection();

// Filtry
$statusFilter = $_GET['status'] ?? '';
$search = sanitizeInput($_GET['search'] ?? '');

// Buduj zapytanie
$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($statusFilter)) {
    $whereClause .= " AND c.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($search)) {
    $whereClause .= " AND (c.complaint_number LIKE ? OR cd.company_name LIKE ? OR cd.first_name LIKE ? OR cd.last_name LIKE ? OR o.order_number LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
    $types .= "sssss";
}

// Pobierz reklamacje
$sql = "
    SELECT c.*, o.order_number, cd.company_name, cd.first_name, cd.last_name, u.email
    FROM complaints c
    JOIN orders o ON c.order_id = o.id
    JOIN users u ON c.user_id = u.id
    JOIN company_data cd ON u.id = cd.user_id
    $whereClause
    ORDER BY c.created_at DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$complaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Statystyki
$stats = [
    'total' => count($complaints),
    'pending' => 0,
    'processing' => 0,
    'accepted' => 0,
    'completed' => 0
];

foreach ($complaints as $complaint) {
    switch ($complaint['status']) {
        case 'Złożona reklamacja':
            $stats['pending']++;
            break;
        case 'Reklamacja rozpatrywana':
            $stats['processing']++;
            break;
        case 'Reklamacja przyjęta':
            $stats['accepted']++;
            break;
        case 'Reklamacja zakończona':
            $stats['completed']++;
            break;
    }
}

// Zapisz aktywność
logActivity($conn, $_SESSION['user_id'], 'view_complaints_admin', 'admin_complaints');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Zarządzanie reklamacjami - Panel Administracyjny</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-cards {
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
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-card.active {
            border: 2px solid var(--admin-primary);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .stat-card.pending .stat-value { color: #f57c00; }
        .stat-card.processing .stat-value { color: #1976d2; }
        .stat-card.accepted .stat-value { color: #388e3c; }
        .stat-card.completed .stat-value { color: #7b1fa2; }
        
        .filters-section {
            background: white;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .complaint-row {
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .complaint-row:hover {
            background: #fafafa;
        }
        
        .complaint-details-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: 500px;
            height: 100%;
            background: white;
            box-shadow: -2px 0 8px rgba(0,0,0,0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1100;
            display: flex;
            flex-direction: column;
        }
        
        .complaint-details-panel.active {
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
            margin-bottom: 24px;
        }
        
        .detail-section h3 {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 12px;
        }
        
        .detail-label {
            font-weight: 500;
            min-width: 120px;
            color: var(--text-secondary);
        }
        
        .detail-value {
            flex: 1;
        }
        
        .status-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        
        .complaint-timeline {
            margin-top: 24px;
        }
        
        .timeline-item {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 12px;
            top: 28px;
            bottom: -16px;
            width: 2px;
            background: #e0e0e0;
        }
        
        .timeline-item:last-child::before {
            display: none;
        }
        
        .timeline-icon {
            width: 24px;
            height: 24px;
            background: var(--admin-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            z-index: 1;
        }
        
        .timeline-content {
            flex: 1;
        }
        
        .timeline-date {
            font-size: 12px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body class="mdc-typography admin">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Zarządzanie reklamacjami</h1>
            </div>
            
            <!-- Statystyki -->
            <div class="stats-cards">
                <div class="stat-card <?php echo $statusFilter === '' ? 'active' : ''; ?>" onclick="filterByStatus('')">
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Wszystkie reklamacje</div>
                </div>
                <div class="stat-card pending <?php echo $statusFilter === 'Złożona reklamacja' ? 'active' : ''; ?>" 
                     onclick="filterByStatus('Złożona reklamacja')">
                    <div class="stat-value"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Oczekujące</div>
                </div>
                <div class="stat-card processing <?php echo $statusFilter === 'Reklamacja rozpatrywana' ? 'active' : ''; ?>" 
                     onclick="filterByStatus('Reklamacja rozpatrywana')">
                    <div class="stat-value"><?php echo $stats['processing']; ?></div>
                    <div class="stat-label">W trakcie</div>
                </div>
                <div class="stat-card accepted <?php echo $statusFilter === 'Reklamacja przyjęta' ? 'active' : ''; ?>" 
                     onclick="filterByStatus('Reklamacja przyjęta')">
                    <div class="stat-value"><?php echo $stats['accepted']; ?></div>
                    <div class="stat-label">Przyjęte</div>
                </div>
                <div class="stat-card completed <?php echo $statusFilter === 'Reklamacja zakończona' ? 'active' : ''; ?>" 
                     onclick="filterByStatus('Reklamacja zakończona')">
                    <div class="stat-value"><?php echo $stats['completed']; ?></div>
                    <div class="stat-label">Zakończone</div>
                </div>
            </div>
            
            <!-- Filtry -->
            <div class="filters-section">
                <div class="mdc-text-field mdc-text-field--outlined mdc-text-field--with-leading-icon">
                    <i class="material-icons mdc-text-field__icon">search</i>
                    <input type="text" 
                           id="search" 
                           class="mdc-text-field__input" 
                           value="<?php echo escape($search); ?>"
                           placeholder="Szukaj po numerze reklamacji, firmie, kliencie...">
                    <div class="mdc-notched-outline">
                        <div class="mdc-notched-outline__leading"></div>
                        <div class="mdc-notched-outline__notch">
                            <label class="mdc-floating-label">Wyszukaj</label>