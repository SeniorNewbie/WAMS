<?php
// index.php - Strona główna z formularzem logowania
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'config/session.php';

initSession();

// Jeśli użytkownik jest już zalogowany, przekieruj
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

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
            $result = loginUser($conn, $email, $password);
            
            if ($result['success']) {
                header('Location: dashboard.php');
                exit();
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Sprawdź czy wyświetlić komunikat o timeout
if (isset($_GET['timeout'])) {
    $error = 'Sesja wygasła. Zaloguj się ponownie.';
}

if (isset($_GET['logout'])) {
    $success = 'Wylogowano pomyślnie.';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel Klienta - Logowanie</title>
    
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
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .login-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo h1 {
            color: #1976d2;
            font-size: 28px;
            margin: 0;
        }
        
        .mdc-text-field {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .mdc-button {
            width: 100%;
            height: 48px;
            margin-top: 20px;
        }
        
        .error-message, .success-message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
        }
        
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: #1976d2;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Panel Klienta</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo escape($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="mdc-text-field mdc-text-field--outlined">
                <input type="email" id="email" name="email" class="mdc-text-field__input" required>
                <div class="mdc-notched-outline">
                    <div class="mdc-notched-outline__leading"></div>
                    <div class="mdc-notched-outline__notch">
                        <label for="email" class="mdc-floating-label">Adres e-mail</label>
                    </div>
                    <div class="mdc-notched-outline__trailing"></div>
                </div>
            </div>
            
            <div class="mdc-text-field mdc-text-field--outlined">
                <input type="password" id="password" name="password" class="mdc-text-field__input" required>
                <div class="mdc-notched-outline">
                    <div class="mdc-notched-outline__leading"></div>
                    <div class="mdc-notched-outline__notch">
                        <label for="password" class="mdc-floating-label">Hasło</label>
                    </div>
                    <div class="mdc-notched-outline__trailing"></div>
                </div>
            </div>
            
            <button type="submit" class="mdc-button mdc-button--raised">
                <span class="mdc-button__label">Zaloguj się</span>
            </button>
        </form>
        
        <div class="forgot-password">
            <a href="forgot-password.php">Zapomniałeś hasła?</a>
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
    </script>
</body>
</html>