<?php
// api/generate_quote.php - API do generowania wycen PDF
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

// Dla demonstracji używamy prostego generowania HTML do PDF
// W produkcji należy użyć biblioteki jak TCPDF, mPDF lub Dompdf

initSession();
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$orderId = intval($_REQUEST['order_id'] ?? 0);

// Pobierz dane zamówienia
$stmt = $conn->prepare("
    SELECT o.*, cd.*, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN company_data cd ON u.id = cd.user_id
    WHERE o.id = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die('Zamówienie nie istnieje');
}

// Sprawdź uprawnienia
if (!isAdmin() && $order['user_id'] != $userId) {
    $stmt = $conn->prepare("SELECT parent_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || $user['parent_id'] != $order['user_id']) {
        die('Brak uprawnień');
    }
}

// Pobierz pozycje zamówienia
$stmt = $conn->prepare("
    SELECT * FROM order_items 
    WHERE order_id = ? 
    ORDER BY position_number
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Zapisz aktywność
logActivity($conn, $userId, 'generate_quote', $order['order_number']);

// Generuj HTML
$html = generateQuoteHTML($order, $items);

// W produkcji tutaj używamy biblioteki PDF
// Przykład z mPDF:
// require_once '../vendor/autoload.php';
// $mpdf = new \Mpdf\Mpdf();
// $mpdf->WriteHTML($html);
// $mpdf->Output('Wycena_' . $order['order_number'] . '.pdf', 'D');

// Tymczasowo zwracamy HTML z nagłówkami PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Wycena_' . $order['order_number'] . '.pdf"');

// Symulacja - w rzeczywistości tutaj byłby wygenerowany PDF
echo "<html><body style='font-family: Arial, sans-serif;'>";
echo $html;
echo "</body></html>";

function generateQuoteHTML($order, $items) {
    $html = '
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-info { margin-bottom: 30px; }
        .client-info { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-size: 18px; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
    
    <div class="header">
        <h1>WYCENA</h1>
        <p>Nr: ' . htmlspecialchars($order['order_number']) . '</p>
        <p>Data: ' . date('d.m.Y') . '</p>
    </div>
    
    <div class="company-info">
        <h3>Wystawca:</h3>
        <p>
            <strong>Nazwa Firmy Sp. z o.o.</strong><br>
            ul. Przykładowa 123<br>
            00-001 Warszawa<br>
            NIP: 1234567890
        </p>
    </div>
    
    <div class="client-info">
        <h3>Klient:</h3>
        <p>
            <strong>' . htmlspecialchars($order['company_name']) . '</strong><br>
            ' . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . '<br>
            ' . htmlspecialchars($order['street'] . ' ' . $order['building_number']) . '<br>
            ' . htmlspecialchars($order['postal_code'] . ' ' . $order['city']) . '<br>
            NIP: ' . htmlspecialchars($order['nip']) . '
        </p>
    </div>
    
    <h3>Pozycje wyceny:</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Lp.</th>
                <th style="width: 25%;">Produkt</th>
                <th style="width: 20%;">Wymiary</th>
                <th style="width: 20%;">Szczegóły</th>
                <th style="width: 10%;">Ilość</th>
                <th style="width: 10%;">Cena jedn.</th>
                <th style="width: 10%;">Wartość</th>
            </tr>
        </thead>
        <tbody>';
    
    $total = 0;
    foreach ($items as $index => $item) {
        $itemTotal = $item['price_net'] * $item['quantity'];
        $total += $itemTotal;
        
        $details = [];
        if ($item['profile_system']) $details[] = 'System: ' . $item['profile_system'];
        if ($item['profile_color']) $details[] = 'Kolor: ' . $item['profile_color'];
        if ($item['net_color']) $details[] = 'Siatka: ' . $item['net_color'];
        
        $html .= '
            <tr>
                <td>' . ($index + 1) . '</td>
                <td>' . htmlspecialchars($item['product']) . '</td>
                <td>' . $item['width'] . ' x ' . $item['height'] . ' mm</td>
                <td style="font-size: 11px;">' . implode('<br>', array_map('htmlspecialchars', $details)) . '</td>
                <td style="text-align: center;">' . $item['quantity'] . '</td>
                <td style="text-align: right;">' . number_format($item['price_net'], 2, ',', ' ') . ' zł</td>
                <td style="text-align: right;">' . number_format($itemTotal, 2, ',', ' ') . ' zł</td>
            </tr>';
    }
    
    $html .= '
        </tbody>
    </table>
    
    <div class="total">
        Razem netto: ' . number_format($total, 2, ',', ' ') . ' zł
    </div>
    
    <div class="footer">
        <p>Wycena ważna 30 dni od daty wystawienia</p>
        <p>Podane ceny są cenami netto, do których należy doliczyć podatek VAT</p>
    </div>';
    
    return $html;
}
?>