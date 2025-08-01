<?php
// client/order_add.php - Formularz dodawania zamówienia
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireClient();
checkSessionTimeout();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Sprawdź uprawnienie do składania zamówień
$stmt = $conn->prepare("
    SELECT COUNT(*) as can_order 
    FROM order_permissions 
    WHERE user_id = ? AND can_order = 1
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$canOrder = $result->fetch_assoc()['can_order'] > 0;

if (!$canOrder) {
    header('Location: orders.php');
    exit();
}

// Pobierz dane użytkownika
$currentUser = getCurrentUser($conn);

// Zapisz aktywność
logActivity($conn, $userId, 'view_order_form', 'orders');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dodaj zamówienie - Panel Klienta</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .order-form {
            background: white;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .form-grid-full {
            grid-column: 1 / -1;
        }
        
        .order-items {
            margin-top: 32px;
        }
        
        .order-item {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 32px;
            gap: 32px;
        }
        
        .summary-box {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 24px;
            flex: 1;
        }
        
        .summary-total {
            font-size: 24px;
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .comment-box {
            flex: 2;
        }
        
        .position-table {
            width: 100%;
            margin-top: 24px;
        }
        
        .mdc-select {
            width: 100%;
        }
        
        .required-field {
            color: var(--error-color);
        }
    </style>
</head>
<body class="mdc-typography">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Nowe zamówienie</h1>
                <a href="orders.php" class="mdc-button">
                    <i class="material-icons mdc-button__icon">arrow_back</i>
                    <span class="mdc-button__label">Powrót</span>
                </a>
            </div>
            
            <form id="orderForm" class="order-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <h2>Dodaj pozycję</h2>
                
                <div class="form-grid">
                    <!-- Produkt -->
                    <div class="mdc-select mdc-select--outlined mdc-select--required">
                        <div class="mdc-select__anchor">
                            <span class="mdc-notched-outline">
                                <span class="mdc-notched-outline__leading"></span>
                                <span class="mdc-notched-outline__notch">
                                    <span class="mdc-floating-label">Produkt <span class="required-field">*</span></span>
                                </span>
                                <span class="mdc-notched-outline__trailing"></span>
                            </span>
                            <span class="mdc-select__selected-text"></span>
                            <span class="mdc-select__dropdown-icon">
                                <i class="material-icons">arrow_drop_down</i>
                            </span>
                        </div>
                        <div class="mdc-select__menu mdc-menu mdc-menu-surface">
                            <ul class="mdc-list">
                                <li class="mdc-list-item" data-value="moskitiera_ramkowa">
                                    <span class="mdc-list-item__text">Moskitiera ramkowa</span>
                                </li>
                            </ul>
                        </div>
                        <input type="hidden" name="product" required>
                    </div>
                    
                    <!-- Szerokość -->
                    <div class="mdc-text-field mdc-text-field--outlined">
                        <input type="number" id="width" name="width" class="mdc-text-field__input" min="1" required>
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="width" class="mdc-floating-label">Szerokość (mm) <span class="required-field">*</span></label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                    
                    <!-- Wysokość -->
                    <div class="mdc-text-field mdc-text-field--outlined">
                        <input type="number" id="height" name="height" class="mdc-text-field__input" min="1" required>
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="height" class="mdc-floating-label">Wysokość (mm) <span class="required-field">*</span></label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                    
                    <!-- Ilość -->
                    <div class="mdc-text-field mdc-text-field--outlined">
                        <input type="number" id="quantity" name="quantity" class="mdc-text-field__input" min="1" value="1" required>
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="quantity" class="mdc-floating-label">Ilość <span class="required-field">*</span></label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                    
                    <!-- System profili -->
                    <div class="mdc-select mdc-select--outlined mdc-select--required">
                        <div class="mdc-select__anchor">
                            <span class="mdc-notched-outline">
                                <span class="mdc-notched-outline__leading"></span>
                                <span class="mdc-notched-outline__notch">
                                    <span class="mdc-floating-label">System profili <span class="required-field">*</span></span>
                                </span>
                                <span class="mdc-notched-outline__trailing"></span>
                            </span>
                            <span class="mdc-select__selected-text"></span>
                            <span class="mdc-select__dropdown-icon">
                                <i class="material-icons">arrow_drop_down</i>
                            </span>
                        </div>
                        <div class="mdc-select__menu mdc-menu mdc-menu-surface">
                            <ul class="mdc-list">
                                <li class="mdc-list-item" data-value="Standard K Moskito">
                                    <span class="mdc-list-item__text">Standard K Moskito</span>
                                </li>
                                <li class="mdc-list-item" data-value="MRSZ_oknaALU">
                                    <span class="mdc-list-item__text">MRSZ_oknaALU</span>
                                </li>
                            </ul>
                        </div>
                        <input type="hidden" name="profile_system" required>
                    </div>
                    
                    <!-- Kolor profili -->
                    <div class="mdc-select mdc-select--outlined mdc-select--required">
                        <div class="mdc-select__anchor">
                            <span class="mdc-notched-outline">
                                <span class="mdc-notched-outline__leading"></span>
                                <span class="mdc-notched-outline__notch">
                                    <span class="mdc-floating-label">Kolor profili <span class="required-field">*</span></span>
                                </span>
                                <span class="mdc-notched-outline__trailing"></span>
                            </span>
                            <span class="mdc-select__selected-text"></span>
                            <span class="mdc-select__dropdown-icon">
                                <i class="material-icons">arrow_drop_down</i>
                            </span>
                        </div>
                        <div class="mdc-select__menu mdc-menu mdc-menu-surface">
                            <ul class="mdc-list">
                                <li class="mdc-list-item" data-value="Biel (9016)"><span class="mdc-list-item__text">Biel (9016)</span></li>
                                <li class="mdc-list-item" data-value="Antracyt (7016)"><span class="mdc-list-item__text">Antracyt (7016)</span></li>
                                <li class="mdc-list-item" data-value="Ciemny brąz (LDR)"><span class="mdc-list-item__text">Ciemny brąz (LDR)</span></li>
                                <li class="mdc-list-item" data-value="Złoty dąb (LDR)"><span class="mdc-list-item__text">Złoty dąb (LDR)</span></li>
                                <li class="mdc-list-item" data-value="Orzech (LDR)"><span class="mdc-list-item__text">Orzech (LDR)</span></li>
                                <li class="mdc-list-item" data-value="Mahoń (LDR)"><span class="mdc-list-item__text">Mahoń (LDR)</span></li>
                                <li class="mdc-list-item" data-value="Winchester (LDR)"><span class="mdc-list-item__text">Winchester (LDR)</span></li>
                                <li class="mdc-list-item" data-value="Dąb słodowy (LDR)"><span class="mdc-list-item__text">Dąb słodowy (LDR)</span></li>
                                <li class="mdc-list-item" data-value="Czekolada (8017)"><span class="mdc-list-item__text">Czekolada (8017)</span></li>
                                <li class="mdc-list-item" data-value="Czarny (9004)"><span class="mdc-list-item__text">Czarny (9004)</span></li>
                                <li class="mdc-list-item" data-value="Silver (9006)"><span class="mdc-list-item__text">Silver (9006)</span></li>
                                <li class="mdc-list-item" data-value="Szary (7039)"><span class="mdc-list-item__text">Szary (7039)</span></li>
                                <li class="mdc-list-item" data-value="Grafit Quartz (GQ01)"><span class="mdc-list-item__text">Grafit Quartz (GQ01)</span></li>
                                <li class="mdc-list-item" data-value="Surowy (0000)"><span class="mdc-list-item__text">Surowy (0000)</span></li>
                                <li class="mdc-list-item" data-value="RAL (dowolny)"><span class="mdc-list-item__text">RAL (dowolny)</span></li>
                            </ul>
                        </div>
                        <input type="hidden" name="profile_color" required>
                    </div>
                    
                    <!-- Kolor siatki -->
                    <div class="mdc-select mdc-select--outlined mdc-select--required">
                        <div class="mdc-select__anchor">
                            <span class="mdc-notched-outline">
                                <span class="mdc-notched-outline__leading"></span>
                                <span class="mdc-notched-outline__notch">
                                    <span class="mdc-floating-label">Kolor siatki <span class="required-field">*</span></span>
                                </span>
                                <span class="mdc-notched-outline__trailing"></span>
                            </span>
                            <span class="mdc-select__selected-text"></span>
                            <span class="mdc-select__dropdown-icon">
                                <i class="material-icons">arrow_drop_down</i>
                            </span>
                        </div>
                        <div class="mdc-select__menu mdc-menu mdc-menu-surface">
                            <ul class="mdc-list">
                                <li class="mdc-list-item" data-value="Czarna">
                                    <span class="mdc-list-item__text">Czarna</span>
                                </li>
                                <li class="mdc-list-item" data-value="Szara">
                                    <span class="mdc-list-item__text">Szara</span>
                                </li>
                            </ul>
                        </div>
                        <input type="hidden" name="net_color" required>
                    </div>
                    
                    <!-- Przylga okna -->
                    <div class="mdc-text-field mdc-text-field--outlined">
                        <input type="number" id="window_rabbet" name="window_rabbet" class="mdc-text-field__input" min="1" required>
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="window_rabbet" class="mdc-floating-label">Przylga okna (mm) <span class="required-field">*</span></label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                    
                    <!-- Pozostałe pola opcjonalne -->
                    <div class="form-grid-full">
                        <h3>Parametry dodatkowe</h3>
                    </div>
                    
                    <!-- Taki będzie zaczep -->
                    <div class="mdc-text-field mdc-text-field--outlined">
                        <input type="number" id="hook_size" name="hook_size" class="mdc-text-field__input" min="1">
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="hook_size" class="mdc-floating-label">Taki będzie zaczep (mm)</label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                    
                    <!-- Nitowanie -->
                    <div class="mdc-select mdc-select--outlined">
                        <div class="mdc-select__anchor">
                            <span class="mdc-notched-outline">
                                <span class="mdc-notched-outline__leading"></span>
                                <span class="mdc-notched-outline__notch">
                                    <span class="mdc-floating-label">Nitowanie</span>
                                </span>
                                <span class="mdc-notched-outline__trailing"></span>
                            </span>
                            <span class="mdc-select__selected-text"></span>
                            <span class="mdc-select__dropdown-icon">
                                <i class="material-icons">arrow_drop_down</i>
                            </span>
                        </div>
                        <div class="mdc-select__menu mdc-menu mdc-menu-surface">
                            <ul class="mdc-list">
                                <li class="mdc-list-item" data-value=""><span class="mdc-list-item__text">-- Wybierz --</span></li>
                                <li class="mdc-list-item" data-value="Tak"><span class="mdc-list-item__text">Tak</span></li>
                                <li class="mdc-list-item" data-value="Nie"><span class="mdc-list-item__text">Nie</span></li>
                                <li class="mdc-list-item" data-value="Odwrócić i nituj"><span class="mdc-list-item__text">Odwrócić i nituj</span></li>
                                <li class="mdc-list-item" data-value="Odwróć nie nituj"><span class="mdc-list-item__text">Odwróć nie nituj</span></li>
                            </ul>
                        </div>
                        <input type="hidden" name="riveting">
                    </div>
                    
                    <!-- Etykieta zamawiającego -->
                    <div class="mdc-text-field mdc-text-field--outlined form-grid-full">
                        <input type="text" id="customer_label" name="customer_label" class="mdc-text-field__input">
                        <div class="mdc-notched-outline">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch">
                                <label for="customer_label" class="mdc-floating-label">Etykieta zamawiającego</label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="addPosition" class="mdc-button mdc-button--raised">
                    <i class="material-icons mdc-button__icon">add</i>
                    <span class="mdc-button__label">Dodaj pozycję</span>
                </button>
                
                <!-- Lista pozycji -->
                <div id="orderItems" class="order-items">
                    <h3>Pozycje zamówienia</h3>
                    <div id="itemsList"></div>
                </div>
                
                <!-- Podsumowanie -->
                <div class="order-summary">
                    <div class="summary-box">
                        <h3>Podsumowanie</h3>
                        <p>Wartość pozycji:</p>
                        <div class="summary-total" id="totalAmount">0,00 zł</div>
                    </div>
                    
                    <div class="summary-box comment-box">
                        <h3>Komentarz do zamówienia</h3>
                        <div class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea">
                            <textarea id="order_comment" name="order_comment" class="mdc-text-field__input" rows="4"></textarea>
                            <div class="mdc-notched-outline">
                                <div class="mdc-notched-outline__leading"></div>
                                <div class="mdc-notched-outline__notch">
                                    <label for="order_comment" class="mdc-floating-label">Dodatkowe uwagi</label>
                                </div>
                                <div class="mdc-notched-outline__trailing"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" id="submitOrder" class="mdc-button mdc-button--raised" disabled>
                        <i class="material-icons mdc-button__icon">shopping_cart</i>
                        <span class="mdc-button__label">Zamawiam</span>
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/order_form.js"></script>
</body>
</html>