<?php
// admin/client_add.php - Formularz dodawania klienta
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/session.php';

initSession();
requireAdmin();
checkSessionTimeout();

$conn = getDBConnection();

// Pobierz listę katalogów
$catalogs = fetchAll($conn, "
    SELECT c1.*, c2.name as parent_name 
    FROM catalogs c1
    LEFT JOIN catalogs c2 ON c1.parent_id = c2.id
    WHERE c1.is_active = 1
    ORDER BY c1.parent_id, c1.name
");

// Pobierz listę produktów dla uprawnień
$products = [
    'Moskitiera ramkowa',
    'Moskitiera drzwiowa',
    'Moskitiera przesuwna',
    'Moskitiera plisowana',
    'Roleta zewnętrzna',
    'Roleta wewnętrzna',
    'Żaluzja pozioma',
    'Żaluzja pionowa'
];

// Zapisz aktywność
logActivity($conn, $_SESSION['user_id'], 'view_client_add_form', 'admin_clients');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dodaj klienta - Panel Administracyjny</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-section {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-section-title {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 24px;
            color: var(--text-primary);
        }
        
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }
        
        .permission-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
        }
        
        .permission-header {
            font-weight: 500;
            margin-bottom: 12px;
        }
        
        .permission-controls {
            display: flex;
            gap: 24px;
        }
        
        .catalog-tree {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
        }
        
        .catalog-group {
            margin-bottom: 16px;
        }
        
        .catalog-parent {
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .catalog-children {
            margin-left: 24px;
        }
        
        .password-info {
            background: #e3f2fd;
            border-radius: 4px;
            padding: 12px;
            margin-top: 8px;
            font-size: 14px;
            color: #1565c0;
        }
        
        .select-all-container {
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .mdc-text-field, .mdc-select {
            width: 100%;
        }
    </style>
</head>
<body class="mdc-typography admin">
    <?php include '../includes/header.php'; ?>
    
    <main class="mdc-top-app-bar--fixed-adjust">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title">Dodaj nowego klienta</h1>
                <a href="clients.php" class="mdc-button">
                    <i class="material-icons mdc-button__icon">arrow_back</i>
                    <span class="mdc-button__label">Powrót</span>
                </a>
            </div>
            
            <form id="clientForm" class="ajax-form" action="../api/admin/client_create.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <!-- Sekcja: Dane dostępowe -->
                <div class="form-section">
                    <h2 class="form-section-title">Dane dostępowe</h2>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="email" id="email" name="email" class="mdc-text-field__input" required>
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="email" class="mdc-floating-label">Adres e-mail (login) *</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="password" id="password" name="password" class="mdc-text-field__input" required minlength="8">
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="password" class="mdc-floating-label">Hasło *</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                            <div class="password-info">
                                <i class="material-icons" style="font-size: 16px; vertical-align: middle;">info</i>
                                Hasło zostanie wysłane na podany adres e-mail
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sekcja: Dane firmy -->
                <div class="form-section">
                    <h2 class="form-section-title">Dane firmy</h2>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="text" id="nip" name="nip" class="mdc-text-field__input" required pattern="[0-9]{10}">
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="nip" class="mdc-floating-label">NIP firmy *</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="text" id="company_name" name="company_name" class="mdc-text-field__input" required>
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="company_name" class="mdc-floating-label">Nazwa firmy *</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="text" id="first_name" name="first_name" class="mdc-text-field__input" required>
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="first_name" class="mdc-floating-label">Imię *</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="text" id="last_name" name="last_name" class="mdc-text-field__input" required>
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="last_name" class="mdc-floating-label">Nazwisko *</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="text" id="street" name="street" class="mdc-text-field__input" required>
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="street" class="mdc-floating-label">Ulica i numer *</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="text" id="building_number" name="building_number" class="mdc-text-field__input">
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="building_number" class="mdc-floating-label">Nr lokalu</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="text" id="postal_code" name="postal_code" class="mdc-text-field__input" required pattern="[0-9]{2}-[0-9]{3}">
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="postal_code" class="mdc-floating-label">Kod pocztowy *</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="text" id="city" name="city" class="mdc-text-field__input" required>
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="city" class="mdc-floating-label">Miejscowość *</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <div class="mdc-select mdc-select--outlined">
                                <div class="mdc-select__anchor">
                                    <span class="mdc-notched-outline">
                                        <span class="mdc-notched-outline__leading"></span>
                                        <span class="mdc-notched-outline__notch">
                                            <span class="mdc-floating-label">Grupa klientów</span>
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
                                        <li class="mdc-list-item" data-value="">
                                            <span class="mdc-list-item__text">-- Wybierz grupę --</span>
                                        </li>
                                        <li class="mdc-list-item" data-value="Klient Moskitiery">
                                            <span class="mdc-list-item__text">Klient Moskitiery</span>
                                        </li>
                                        <li class="mdc-list-item" data-value="Klient Rolety">
                                            <span class="mdc-list-item__text">Klient Rolety</span>
                                        </li>
                                        <li class="mdc-list-item" data-value="Klient Roletki">
                                            <span class="mdc-list-item__text">Klient Roletki</span>
                                        </li>
                                        <li class="mdc-list-item" data-value="Klient Żaluzje">
                                            <span class="mdc-list-item__text">Klient Żaluzje</span>
                                        </li>
                                        <li class="mdc-list-item" data-value="Klient Duży">
                                            <span class="mdc-list-item__text">Klient Duży</span>
                                        </li>
                                    </ul>
                                </div>
                                <input type="hidden" name="client_group">
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <div class="mdc-text-field mdc-text-field--outlined">
                                <input type="text" id="country" name="country" class="mdc-text-field__input" value="Polska">
                                <div class="mdc-notched-outline">
                                    <div class="mdc-notched-outline__leading"></div>
                                    <div class="mdc-notched-outline__notch">
                                        <label for="country" class="mdc-floating-label">Kraj</label>
                                    </div>
                                    <div class="mdc-notched-outline__trailing"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sekcja: Uprawnienia do katalogów -->
                <div class="form-section">
                    <h2 class="form-section-title">Uprawnienia do katalogów</h2>
                    
                    <div class="select-all-container">
                        <div class="mdc-form-field">
                            <div class="mdc-checkbox">
                                <input type="checkbox"
                                       class="mdc-checkbox__native-control"
                                       id="select_all_catalogs">
                                <div class="mdc-checkbox__background">
                                    <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                        <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                    </svg>
                                    <div class="mdc-checkbox__mixedmark"></div>
                                </div>
                            </div>
                            <label for="select_all_catalogs">
                                <strong>Zaznacz wszystkie katalogi</strong>
                            </label>
                        </div>
                    </div>
                    
                    <div class="catalog-tree">
                        <?php 
                        $parentCatalogs = [];
                        $childCatalogs = [];
                        
                        foreach ($catalogs as $catalog) {
                            if ($catalog['parent_id'] === null) {
                                $parentCatalogs[] = $catalog;
                            } else {
                                $childCatalogs[$catalog['parent_id']][] = $catalog;
                            }
                        }
                        
                        foreach ($parentCatalogs as $parent): 
                        ?>
                        <div class="catalog-group">
                            <div class="mdc-form-field">
                                <div class="mdc-checkbox">
                                    <input type="checkbox"
                                           class="mdc-checkbox__native-control catalog-parent-checkbox"
                                           id="catalog_<?php echo $parent['id']; ?>"
                                           name="catalog_permissions[]"
                                           value="<?php echo $parent['id']; ?>">
                                    <div class="mdc-checkbox__background">
                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                        </svg>
                                        <div class="mdc-checkbox__mixedmark"></div>
                                    </div>
                                </div>
                                <label for="catalog_<?php echo $parent['id']; ?>" class="catalog-parent">
                                    <?php echo escape($parent['name']); ?>
                                </label>
                            </div>
                            
                            <?php if (isset($childCatalogs[$parent['id']])): ?>
                            <div class="catalog-children">
                                <?php foreach ($childCatalogs[$parent['id']] as $child): ?>
                                <div class="mdc-form-field">
                                    <div class="mdc-checkbox">
                                        <input type="checkbox"
                                               class="mdc-checkbox__native-control catalog-child-checkbox"
                                               data-parent="<?php echo $parent['id']; ?>"
                                               id="catalog_<?php echo $child['id']; ?>"
                                               name="catalog_permissions[]"
                                               value="<?php echo $child['id']; ?>">
                                        <div class="mdc-checkbox__background">
                                            <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                            </svg>
                                            <div class="mdc-checkbox__mixedmark"></div>
                                        </div>
                                    </div>
                                    <label for="catalog_<?php echo $child['id']; ?>">
                                        <?php echo escape($child['name']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Sekcja: Uprawnienia do modułu zamówień -->
                <div class="form-section">
                    <h2 class="form-section-title">Uprawnienia do modułu zamówień</h2>
                    
                    <div class="select-all-container">
                        <div class="mdc-form-field">
                            <div class="mdc-checkbox">
                                <input type="checkbox"
                                       class="mdc-checkbox__native-control"
                                       id="select_all_products">
                                <div class="mdc-checkbox__background">
                                    <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                        <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                    </svg>
                                    <div class="mdc-checkbox__mixedmark"></div>
                                </div>
                            </div>
                            <label for="select_all_products">
                                <strong>Nadaj pełne uprawnienia do wszystkich produktów</strong>
                            </label>
                        </div>
                    </div>
                    
                    <div class="permissions-grid">
                        <?php foreach ($products as $product): ?>
                        <div class="permission-item">
                            <div class="permission-header"><?php echo escape($product); ?></div>
                            <div class="permission-controls">
                                <div class="mdc-form-field">
                                    <div class="mdc-checkbox">
                                        <input type="checkbox"
                                               class="mdc-checkbox__native-control product-quote-checkbox"
                                               id="quote_<?php echo md5($product); ?>"
                                               name="permissions[<?php echo escape($product); ?>][quote]"
                                               value="1">
                                        <div class="mdc-checkbox__background">
                                            <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                            </svg>
                                            <div class="mdc-checkbox__mixedmark"></div>
                                        </div>
                                    </div>
                                    <label for="quote_<?php echo md5($product); ?>">Wycena</label>
                                </div>
                                
                                <div class="mdc-form-field">
                                    <div class="mdc-checkbox">
                                        <input type="checkbox"
                                               class="mdc-checkbox__native-control product-order-checkbox"
                                               id="order_<?php echo md5($product); ?>"
                                               name="permissions[<?php echo escape($product); ?>][order]"
                                               value="1">
                                        <div class="mdc-checkbox__background">
                                            <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                            </svg>
                                            <div class="mdc-checkbox__mixedmark"></div>
                                        </div>
                                    </div>
                                    <label for="order_<?php echo md5($product); ?>">Zamówienie</label>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Przyciski -->
                <div class="form-actions" style="text-align: right; margin-top: 32px;">
                    <a href="clients.php" class="mdc-button">
                        <span class="mdc-button__label">Anuluj</span>
                    </a>
                    <button type="submit" class="mdc-button mdc-button--raised">
                        <i class="material-icons mdc-button__icon">save</i>
                        <span class="mdc-button__label">Utwórz konto klienta</span>
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <script src="../assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Inicjalizacja Material Components
            initializeMDC();
            
            // Zaznacz wszystkie katalogi
            $('#select_all_catalogs').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.catalog-parent-checkbox, .catalog-child-checkbox').prop('checked', isChecked);
            });
            
            // Zaznacz wszystkie produkty
            $('#select_all_products').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.product-quote-checkbox, .product-order-checkbox').prop('checked', isChecked);
            });
            
            // Obsługa zaznaczania katalogów nadrzędnych
            $('.catalog-parent-checkbox').on('change', function() {
                const parentId = $(this).val();
                const isChecked = $(this).prop('checked');
                
                // Zaznacz/odznacz wszystkie podkatalogi
                $(`.catalog-child-checkbox[data-parent="${parentId}"]`).prop('checked', isChecked);
                
                // Zaktualizuj stan "zaznacz wszystkie"
                updateSelectAllState();
            });
            
            // Obsługa zaznaczania podkatalogów
            $('.catalog-child-checkbox').on('change', function() {
                const parentId = $(this).data('parent');
                const parentCheckbox = $(`#catalog_${parentId}`);
                
                // Sprawdź czy wszystkie podkatalogi są zaznaczone
                const allChildren = $(`.catalog-child-checkbox[data-parent="${parentId}"]`);
                const checkedChildren = allChildren.filter(':checked');
                
                if (checkedChildren.length === 0) {
                    parentCheckbox.prop('checked', false);
                    parentCheckbox.prop('indeterminate', false);
                } else if (checkedChildren.length === allChildren.length) {
                    parentCheckbox.prop('checked', true);
                    parentCheckbox.prop('indeterminate', false);
                } else {
                    parentCheckbox.prop('checked', false);
                    parentCheckbox.prop('indeterminate', true);
                }
                
                // Zaktualizuj stan "zaznacz wszystkie"
                updateSelectAllState();
            });
            
            // Funkcja aktualizująca stan "zaznacz wszystkie"
            function updateSelectAllState() {
                const allCatalogs = $('.catalog-parent-checkbox, .catalog-child-checkbox');
                const checkedCatalogs = allCatalogs.filter(':checked');
                
                if (checkedCatalogs.length === 0) {
                    $('#select_all_catalogs').prop('checked', false);
                    $('#select_all_catalogs').prop('indeterminate', false);
                } else if (checkedCatalogs.length === allCatalogs.length) {
                    $('#select_all_catalogs').prop('checked', true);
                    $('#select_all_catalogs').prop('indeterminate', false);
                } else {
                    $('#select_all_catalogs').prop('checked', false);
                    $('#select_all_catalogs').prop('indeterminate', true);
                }
            }
            
            // Aktualizacja stanu "zaznacz wszystkie produkty"
            $('.product-quote-checkbox, .product-order-checkbox').on('change', function() {
                const allProductCheckboxes = $('.product-quote-checkbox, .product-order-checkbox');
                const checkedProductCheckboxes = allProductCheckboxes.filter(':checked');
                
                if (checkedProductCheckboxes.length === 0) {
                    $('#select_all_products').prop('checked', false);
                    $('#select_all_products').prop('indeterminate', false);
                } else if (checkedProductCheckboxes.length === allProductCheckboxes.length) {
                    $('#select_all_products').prop('checked', true);
                    $('#select_all_products').prop('indeterminate', false);
                } else {
                    $('#select_all_products').prop('checked', false);
                    $('#select_all_products').prop('indeterminate', true);
                }
            });
            
            // Generowanie hasła
            function generatePassword() {
                const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
                let password = '';
                for (let i = 0; i < 12; i++) {
                    password += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return password;
            }
            
            // Ustaw automatycznie generowane hasło
            $('#password').val(generatePassword());
            
            // Przycisk generowania nowego hasła
            $('<button type="button" class="mdc-button" id="generate-password" style="margin-top: 8px;"><i class="material-icons mdc-button__icon">refresh</i>Generuj nowe hasło</button>')
                .insertAfter('#password')
                .closest('.mdc-text-field')
                .on('click', '#generate-password', function() {
                    $('#password').val(generatePassword());
                });
            
            // Walidacja formularza
            $('#clientForm').on('submit', function(e) {
                // Resetuj błędy
                $('.mdc-text-field--invalid').removeClass('mdc-text-field--invalid');
                
                // Dodatkowa walidacja NIP
                const nip = $('#nip').val().replace(/[^0-9]/g, '');
                if (!isValidNIP(nip)) {
                    e.preventDefault();
                    showNotification('error', 'Nieprawidłowy numer NIP');
                    $('#nip').closest('.mdc-text-field').addClass('mdc-text-field--invalid');
                    return false;
                }
                
                // Walidacja kodu pocztowego
                const postalCode = $('#postal_code').val();
                if (!/^\d{2}-\d{3}$/.test(postalCode)) {
                    e.preventDefault();
                    showNotification('error', 'Nieprawidłowy format kodu pocztowego (XX-XXX)');
                    $('#postal_code').closest('.mdc-text-field').addClass('mdc-text-field--invalid');
                    return false;
                }
                
                // Walidacja email
                const email = $('#email').val();
                if (!isValidEmail(email)) {
                    e.preventDefault();
                    showNotification('error', 'Nieprawidłowy adres email');
                    $('#email').closest('.mdc-text-field').addClass('mdc-text-field--invalid');
                    return false;
                }
                
                // Walidacja hasła
                const password = $('#password').val();
                if (password.length < 8) {
                    e.preventDefault();
                    showNotification('error', 'Hasło musi mieć co najmniej 8 znaków');
                    $('#password').closest('.mdc-text-field').addClass('mdc-text-field--invalid');
                    return false;
                }
                
                // Sprawdź czy wybrano przynajmniej jedno uprawnienie
                const hasPermissions = $('.catalog-parent-checkbox:checked, .catalog-child-checkbox:checked').length > 0 || 
                                     $('.product-quote-checkbox:checked, .product-order-checkbox:checked').length > 0;
                
                if (!hasPermissions) {
                    e.preventDefault();
                    if (!confirm('Nie przyznano żadnych uprawnień. Czy na pewno chcesz utworzyć konto bez uprawnień?')) {
                        return false;
                    }
                }
            });
            
            // Automatyczne formatowanie NIP
            $('#nip').on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, '');
                if (value.length > 10) {
                    value = value.slice(0, 10);
                }
                $(this).val(value);
            });
            
            // Automatyczne formatowanie kodu pocztowego
            $('#postal_code').on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, '');
                if (value.length > 5) {
                    value = value.slice(0, 5);
                }
                if (value.length >= 2) {
                    value = value.slice(0, 2) + '-' + value.slice(2);
                }
                $(this).val(value);
            });
            
            // Pokaż/ukryj hasło
            $('<button type="button" class="mdc-icon-button material-icons" id="toggle-password" style="position: absolute; right: 8px; top: 16px;">visibility_off</button>')
                .appendTo('#password').closest('.mdc-text-field')
                .on('click', function() {
                    const passwordField = $('#password');
                    const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                    passwordField.attr('type', type);
                    $(this).text(type === 'password' ? 'visibility_off' : 'visibility');
                });
            
            // Automatyczne wypełnianie danych testowych (tylko w trybie deweloperskim)
            if (window.location.hostname === 'localhost') {
                $('#email').val('test@example.com');
                $('#nip').val('1234567890');
                $('#company_name').val('Firma Testowa Sp. z o.o.');
                $('#first_name').val('Jan');
                $('#last_name').val('Kowalski');
                $('#street').val('Testowa 123');
                $('#postal_code').val('00-001');
                $('#city').val('Warszawa');
            }
        });
    </script>
</body>
</html>