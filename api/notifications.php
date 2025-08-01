<?php
// api/notifications.php - API do obsługi powiadomień
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

header('Content-Type: application/json');

initSession();
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? 'list';

switch ($action) {
    case 'check':
        // Sprawdź liczbę nieprzeczytanych powiadomień
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        echo json_encode(['count' => $data['count']]);
        break;
        
    case 'list':
        // Pobierz listę powiadomień
        $stmt = $conn->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        // Zwróć HTML
        header('Content-Type: text/html');
        if (empty($notifications)) {
            echo '<div class="text-center p-4">Brak powiadomień</div>';
        } else {
            foreach ($notifications as $notification) {
                $typeIcons = [
                    'new_file' => 'insert_drive_file',
                    'new_invoice' => 'receipt',
                    'new_promotion' => 'local_offer',
                    'new_order' => 'shopping_cart',
                    'new_complaint' => 'report_problem'
                ];
                
                $icon = $typeIcons[$notification['type']] ?? 'notifications';
                $unreadClass = !$notification['is_read'] ? 'unread' : '';
                $timeAgo = getTimeAgo($notification['created_at']);
                
                echo <<<HTML
                <div class="notification-item {$unreadClass}" data-id="{$notification['id']}">
                    <div class="notification-type {$notification['type']}">
                        <i class="material-icons">{$icon}</i>
                        {$notification['type']}
                    </div>
                    <div class="notification-title">{$notification['title']}</div>
                    <div class="notification-message">{$notification['message']}</div>
                    <div class="notification-time">{$timeAgo}</div>
                </div>
HTML;
            }
        }
        break;
        
    case 'mark_read':
        // Oznacz wszystkie jako przeczytane
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        
        echo json_encode(['success' => $success]);
        break;
        
    case 'mark_single_read':
        // Oznacz pojedyncze powiadomienie jako przeczytane
        $notificationId = intval($_POST['notification_id'] ?? 0);
        
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);
        $success = $stmt->execute();
        
        echo json_encode(['success' => $success]);
        break;
        
    case 'delete':
        // Usuń powiadomienie
        $notificationId = intval($_POST['notification_id'] ?? 0);
        
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);
        $success = $stmt->execute();
        
        echo json_encode(['success' => $success]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}

// Funkcja pomocnicza do formatowania czasu
function getTimeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return "przed chwilą";
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . " min temu";
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . " godz. temu";
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . " dni temu";
    } else {
        return date('d.m.Y H:i', $timestamp);
    }
}
?>