<?php
// api/orders.php - API do obsługi zamówień
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

header('Content-Type: application/json');

initSession();
requireLogin();

$conn = getDBConnection();
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_order_items':
        getOrderItems($conn);
        break;
        
    case 'get_user_orders':
        getUserOrders($conn);
        break;
        
    case 'calculate_price':
        calculatePrice($conn);
        break;
        
    case 'validate_order':
        validateOrder($conn);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function getOrderItems($conn) {
    $orderId = intval($_GET['order_id'] ?? 0);
    
    // Sprawdź uprawnienia
    $stmt = $conn->prepare("
        SELECT user_id FROM orders WHERE id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order || ($order['user_id'] != $_SESSION['user_id'] && !isAdmin())) {
        echo json_encode(['error' => 'Brak uprawnień']);
        return;
    }
    
    // Pobierz pozycje
    $stmt = $conn->prepare("
        SELECT * FROM order_items 
        WHERE order_id = ? 
        ORDER BY position_number
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
}

function getUserOrders($conn) {
    $userId = $_SESSION['user_id'];
    $page = intval($_GET['page'] ?? 1);
    $perPage = intval($_GET['per_page'] ?? 20);
    $offset = ($page - 1) * $perPage;
    
    // Filtry
    $status = sanitizeInput($_GET['status'] ?? '');
    $dateFrom = sanitizeInput($_GET['date_from'] ?? '');
    $dateTo = sanitizeInput($_GET['date_to'] ?? '');
    
    $whereClause = "WHERE o.user_id = ?";
    $params = [$userId];
    $types = "i";
    
    if (!empty($status)) {
        $whereClause .= " AND o.order_status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if (!empty($dateFrom)) {
        $whereClause .= " AND o.created_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
        $types .= "s";
    }
    
    if (!empty($dateTo)) {
        $whereClause .= " AND o.created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
        $types .= "s";
    }
    
    // Pobierz zamówienia
    $sql = "
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
        FROM orders o
        $whereClause
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $perPage;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    // Policz wszystkie
    $countSql = "SELECT COUNT(*) as total FROM orders o $whereClause";
    array_pop($params); // Usuń limit
    array_pop($params); // Usuń offset
    $types = substr($types, 0, -2);
    
    $stmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'pages' => ceil($total / $perPage)
        ]
    ]);
}

function calculatePrice($conn) {
    $product = sanitizeInput($_POST['product'] ?? '');
    $width = intval($_POST['width'] ?? 0);
    $height = intval($_POST['height'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $options = $_POST['options'] ?? [];
    
    // Sprawdź uprawnienia do produktu
    $stmt = $conn->prepare("
        SELECT can_quote, can_order 
        FROM order_permissions 
        WHERE user_id = ? AND product_name = ?
    ");
    $stmt->bind_param("is", $_SESSION['user_id'], $product);
    $stmt->execute();
    $result = $stmt->get_result();
    $permission = $result->fetch_assoc();
    
    if (!$permission || (!$permission['can_quote'] && !$permission['can_order'])) {
        echo json_encode(['error' => 'Brak uprawnień do wyceny tego produktu']);
        return;
    }
    
    // Przykładowa kalkulacja ceny
    $basePrice = calculateBasePrice($product, $width, $height);
    $optionsPrice = calculateOptionsPrice($options);
    $totalPrice = ($basePrice + $optionsPrice) * $quantity;
    
    echo json_encode([
        'success' => true,
        'price' => [
            'base' => $basePrice,
            'options' => $optionsPrice,
            'unit' => $basePrice + $optionsPrice,
            'total' => $totalPrice
        ],
        'can_order' => $permission['can_order']
    ]);
}

function calculateBasePrice($product, $width, $height) {
    // Przykładowe ceny bazowe za m²
    $pricesPerM2 = [
        'Moskitiera ramkowa' => 120,
        'Moskitiera drzwiowa' => 180,
        'Moskitiera przesuwna' => 250,
        'Moskitiera plisowana' => 350,
        'Roleta zewnętrzna' => 450,
        'Roleta wewnętrzna' => 280,
        'Żaluzja pozioma' => 150,
        'Żaluzja pionowa' => 200
    ];
    
    $pricePerM2 = $pricesPerM2[$product] ?? 100;
    $area = ($width * $height) / 1000000; // m²
    
    // Minimalna cena
    $minPrice = 50;
    
    return max($area * $pricePerM2, $minPrice);
}

function calculateOptionsPrice($options) {
    $additionalPrice = 0;
    
    // Dodatkowe opłaty za opcje
    if (isset($options['profile_color']) && $options['profile_color'] === 'RAL (dowolny)') {
        $additionalPrice += 50;
    }
    
    if (isset($options['self_assembly']) && !$options['self_assembly']) {
        $additionalPrice += 30;
    }
    
    if (isset($options['horizontal_bar']) && $options['horizontal_bar']) {
        $additionalPrice += 25;
    }
    
    return $additionalPrice;
}

function validateOrder($conn) {
    $items = $_POST['items'] ?? [];
    
    if (empty($items)) {
        echo json_encode([
            'success' => false,
            'errors' => ['items' => 'Brak pozycji w zamówieniu']
        ]);
        return;
    }
    
    $errors = [];
    $warnings = [];
    
    foreach ($items as $index => $item) {
        // Walidacja wymiarów
        if ($item['width'] < 100 || $item['width'] > 3000) {
            $errors["item_$index"] = "Nieprawidłowa szerokość (100-3000mm)";
        }
        
        if ($item['height'] < 100 || $item['height'] > 3000) {
            $errors["item_$index"] = "Nieprawidłowa wysokość (100-3000mm)";
        }
        
        // Ostrzeżenia
        if ($item['width'] > 2000 || $item['height'] > 2000) {
            $warnings[] = "Pozycja " . ($index + 1) . ": Duże wymiary mogą wymagać specjalnego transportu";
        }
    }
    
    echo json_encode([
        'success' => empty($errors),
        'errors' => $errors,
        'warnings' => $warnings
    ]);
}
?>