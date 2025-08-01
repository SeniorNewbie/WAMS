<?php
// config/security.php

// Funkcja do bezpiecznego hashowania haseł
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Weryfikacja hasła
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generowanie bezpiecznego tokenu sesji
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Ochrona przed SQL Injection
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Walidacja adresu email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Sprawdzanie siły hasła
function validatePasswordStrength($password) {
    // Minimum 8 znaków, jedna wielka litera, jedna mała, jedna cyfra, jeden znak specjalny
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

// Ochrona przed CSRF
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateSecureToken();
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Logowanie aktywności
function logActivity($conn, $userId, $action, $module = null) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    // Jeśli userId jest null, użyj NULL w zapytaniu
    if ($userId === null) {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip_address, user_agent) VALUES (NULL, ?, ?, ?, ?)");
        $stmt->bind_param("ssss", $action, $module, $ip, $userAgent);
    } else {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $action, $module, $ip, $userAgent);
    }
    
    $stmt->execute();
    $stmt->close();
}

// Sprawdzanie uprawnień
function checkPermission($conn, $userId, $module, $action = 'view') {
    // Administratorzy mają pełne uprawnienia
    $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user['user_type'] === 'admin') {
        return true;
    }
    
    // Sprawdzanie konkretnych uprawnień dla modułów
    switch ($module) {
        case 'catalogs':
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM catalog_permissions WHERE user_id = ? AND can_view = 1");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data['count'] > 0;
            
        case 'orders':
            if ($action === 'create') {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_permissions WHERE user_id = ? AND can_order = 1");
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_permissions WHERE user_id = ? AND (can_quote = 1 OR can_order = 1)");
            }
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data['count'] > 0;
            
        default:
            return true; // Domyślnie zezwalaj na dostęp do podstawowych modułów
    }
}

// Zabezpieczenie przed brute force
function checkLoginAttempts($conn, $email) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $timeLimit = date('Y-m-d H:i:s', strtotime('-15 minutes'));
    
    // Sprawdź liczbę prób logowania w ostatnich 15 minutach
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM activity_logs WHERE ip_address = ? AND action = 'failed_login' AND created_at > ?");
    $stmt->bind_param("ss", $ip, $timeLimit);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    return $data['attempts'] < 5; // Maksymalnie 5 prób w ciągu 15 minut
}

// Czyszczenie starych sesji
function cleanupSessions($conn) {
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
    $stmt->execute();
    $stmt->close();
}

// Walidacja NIP
function validateNIP($nip) {
    $nip = preg_replace('/[^0-9]/', '', $nip);
    
    if (strlen($nip) !== 10) {
        return false;
    }
    
    $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
    $sum = 0;
    
    for ($i = 0; $i < 9; $i++) {
        $sum += $nip[$i] * $weights[$i];
    }
    
    $control = $sum % 11;
    if ($control == 10) {
        $control = 0;
    }
    
    return $control == $nip[9];
}

// Bezpieczne przesyłanie plików
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'], $maxSize = 5242880) {
    // Sprawdź błędy
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Błąd podczas przesyłania pliku'];
    }
    
    // Sprawdź rozmiar (domyślnie 5MB)
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Plik jest za duży'];
    }
    
    // Sprawdź typ pliku
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'message' => 'Niedozwolony typ pliku'];
    }
    
    // Sprawdź rzeczywisty typ MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf'
    ];
    
    if (!isset($allowedMimes[$extension]) || $allowedMimes[$extension] !== $mimeType) {
        return ['success' => false, 'message' => 'Nieprawidłowy typ pliku'];
    }
    
    return ['success' => true];
}

// Debug logging dla administratora
function debugLog($conn, $level, $message, $file = null, $line = null) {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO debug_logs (error_level, message, file, line, user_id, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiis", $level, $message, $file, $line, $userId, $ip);
    $stmt->execute();
    $stmt->close();
}

// Funkcja do bezpiecznego wyświetlania danych
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Walidacja danych wejściowych dla formularzy
function validateFormData($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        if (isset($rule['required']) && $rule['required'] && empty($data[$field])) {
            $errors[$field] = "Pole jest wymagane";
            continue;
        }
        
        if (!empty($data[$field])) {
            // Walidacja typu
            if (isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'email':
                        if (!validateEmail($data[$field])) {
                            $errors[$field] = "Nieprawidłowy adres email";
                        }
                        break;
                    case 'numeric':
                        if (!is_numeric($data[$field])) {
                            $errors[$field] = "Wartość musi być liczbą";
                        }
                        break;
                    case 'nip':
                        if (!validateNIP($data[$field])) {
                            $errors[$field] = "Nieprawidłowy NIP";
                        }
                        break;
                }
            }
            
            // Walidacja długości
            if (isset($rule['min_length']) && strlen($data[$field]) < $rule['min_length']) {
                $errors[$field] = "Minimalna długość: " . $rule['min_length'] . " znaków";
            }
            
            if (isset($rule['max_length']) && strlen($data[$field]) > $rule['max_length']) {
                $errors[$field] = "Maksymalna długość: " . $rule['max_length'] . " znaków";
            }
        }
    }
    
    return $errors;
}
?>