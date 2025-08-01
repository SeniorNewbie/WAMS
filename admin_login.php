<?php
// je832hgf3.php - Ukryta strona logowania administratora
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'config/session.php';

initSession();

// Jeśli użytkownik jest już zalogowany jako admin, przekieruj
if (isLoggedIn() && isAdmin()) {
    header('Location: admin/index.php');
    exit();
}

$error = '';

// Obsługa formularza logowania
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Błąd bezpieczeństwa. Spróbuj ponownie.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Wprowadź email i hasło';
        } else {
            $conn = getDBConnection();
            
            // Sprawdź czy to administrator
            $stmt = $conn->prepare("SELECT user_type FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (!$user || $user['user_type'] !== 'admin') {
                $error = 'Brak uprawnień administratora';
                logActivity($conn, null, 'unauthorized_admin_access', $email);
            } else {
                $loginResult = loginUser($conn, $email, $password);
                
                if ($loginResult['success']) {
                    header('Location: admin/index.php');
                    exit();
                } else {
                    $error = $loginResult['message'];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel Administracyjny</title>
    
    <!-- Material Components Web -->
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body {
            margin: 0;
            font-family: Roboto, sans-serif;
            background: #263238;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .login-container {
            background: #37474f;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo h1 {
            color: #ff5252;
            font-size: 28px;
            margin: 0;
        }
        
        .logo .material-icons {
            font-size: 48px;
            color: #ff5252;
            margin-bottom: 10px;
        }
        
        .mdc-text-field {
            width: 100%;
            margin-bottom: 20px;
            --mdc-theme-primary: #ff5252;
        }
        
        .mdc-text-field--outlined:not(.mdc-text-field--disabled) .mdc-notched-outline__leading,
        .mdc-text-field--outlined:not(.mdc-text-field--disabled) .mdc-notched-outline__notch,
        .mdc-text-field--outlined:not(.mdc-text-field--disabled) .mdc-notched-outline__trailing {
            border-color: #90a4ae;
        }
        
        .mdc-text-field__input {
            color: white;
        }
        
        .mdc-floating-label {
            color: #b0bec5;
        }
        
        .mdc-button {
            width: 100%;
            height: 48px;
            margin-top: 20px;
            --mdc-theme-primary: #ff5252;
            --mdc-theme-on-primary: white;
        }
        
        .error-message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            background: rgba(229, 57, 53, 0.1);
            color: #ff8a80;
            border: 1px solid #ff5252;
        }
        
        .security-notice {
            text-align: center;
            color: #90a4ae;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="material-icons">admin_panel_settings</i>
            <h1>Panel Administracyjny</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="mdc-text-field mdc-text-field--outlined">
                <input type="email" id="email" name="email" class="mdc-text-field__input" required autocomplete="off">
                <div class="mdc-notched-outline">
                    <div class="mdc-notched-outline__leading"></div>
                    <div class="mdc-notched-outline__notch">
                        <label for="email" class="mdc-floating-label">Adres e-mail administratora</label>
                    </div>
                    <div class="mdc-notched-outline__trailing"></div>
                </div>
            </div>
            
            <div class="mdc-text-field mdc-text-field--outlined">
                <input type="password" id="password" name="password" class="mdc-text-field__input" required autocomplete="off">
                <div class="mdc-notched-outline">
                    <div class="mdc-notched-outline__leading"></div>
                    <div class="mdc-notched-outline__notch">
                        <label for="password" class="mdc-floating-label">Hasło</label>
                    </div>
                    <div class="mdc-notched-outline__trailing"></div>
                </div>
            </div>
            
            <button type="submit" class="mdc-button mdc-button--raised">
                <span class="mdc-button__label">Zaloguj jako Administrator</span>
            </button>
        </form>
        
        <div class="security-notice">
            <i class="material-icons" style="font-size: 16px; vertical-align: middle;">security</i>
            Wszystkie próby logowania są monitorowane
        </div>
    </div>
    
    <script>
        // Inicjalizacja Material Components
        document.querySelectorAll('.mdc-text-field').forEach(function(el) {
            new mdc.textField.MDCTextField(el);
        });
        
        document.querySelectorAll('.mdc-button').forEach(function(el) {
            new mdc.ripple.MDCRipple(el);
        });
        
        // Dodatkowe zabezpieczenie - wyłącz menu kontekstowe
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });
    </script>
</body>
</html>