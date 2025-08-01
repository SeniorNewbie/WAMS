<?php
// config/session.php
require_once 'database.php';
require_once 'security.php';

// Konfiguracja sesji
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Tylko HTTPS w produkcji
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// Własna obsługa sesji w bazie danych
class DatabaseSessionHandler implements SessionHandlerInterface {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function open($save_path, $session_name) {
        return true;
    }
    
    public function close() {
        return true;
    }
    
    public function read($session_id) {
        $stmt = $this->conn->prepare("SELECT data FROM user_sessions WHERE session_token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['data'];
        }
        
        return '';
    }
    
    public function write($session_id, $session_data) {
        $expires_at = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        // Sprawdź czy sesja już istnieje
        $stmt = $this->conn->prepare("SELECT id FROM user_sessions WHERE session_token = ?");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Aktualizuj istniejącą sesję
            $stmt = $this->conn->prepare("
                UPDATE user_sessions 
                SET data = ?, expires_at = ?, user_id = ?, ip_address = ?, user_agent = ?
                WHERE session_token = ?
            ");
            $stmt->bind_param("ssisss", $session_data, $expires_at, $user_id, $ip, $user_agent, $session_id);
        } else {
            // Utwórz nową sesję
            $stmt = $this->conn->prepare("
                INSERT INTO user_sessions (session_token, data, expires_at, user_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssiss", $session_id, $session_data, $expires_at, $user_id, $ip, $user_agent);
        }
        
        return $stmt->execute();
    }
    
    public function destroy($session_id) {
        $stmt = $this->conn->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        $stmt->bind_param("s", $session_id);
        return $stmt->execute();
    }
    
    public function gc($maxlifetime) {
        $stmt = $this->conn->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
        return $stmt->execute();
    }
}

// Inicjalizacja sesji
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        $conn = getDBConnection();
        $handler = new DatabaseSessionHandler($conn);
        session_set_save_handler($handler, true);
        session_start();
        
        // Regeneruj ID sesji co 30 minut
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// Sprawdzanie czy użytkownik jest zalogowany
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Sprawdzanie typu użytkownika
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isClient() {
    return isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'client' || $_SESSION['user_type'] === 'subaccount');
}

// Wymuszenie logowania
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit();
    }
}

// Wymuszenie uprawnień administratora
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /dashboard.php');
        exit();
    }
}

// Wymuszenie uprawnień klienta
function requireClient() {
    requireLogin();
    if (!isClient()) {
        header('Location: /admin/index.php');
        exit();
    }
}

// Logowanie użytkownika
function loginUser($conn, $email, $password) {
    // Sprawdź próby logowania
    if (!checkLoginAttempts($conn, $email)) {
        return ['success' => false, 'message' => 'Zbyt wiele nieudanych prób. Spróbuj ponownie za 15 minut.'];
    }
    
    // Pobierz dane użytkownika
    $stmt = $conn->prepare("SELECT id, password, user_type, is_active FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (!$user['is_active']) {
            logActivity($conn, $user['id'], 'failed_login', 'account_inactive');
            return ['success' => false, 'message' => 'Konto jest nieaktywne'];
        }
        
        if (verifyPassword($password, $user['password'])) {
            // Zaloguj użytkownika
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_email'] = $email;
            
            // Aktualizuj ostatnie logowanie
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            
            // Zapisz w logach
            logActivity($conn, $user['id'], 'login', 'success');
            
            return ['success' => true, 'user_type' => $user['user_type']];
        } else {
            // Nieudane logowanie - złe hasło
            logActivity($conn, null, 'failed_login', $email);
            return ['success' => false, 'message' => 'Nieprawidłowy email lub hasło'];
        }
    }
    
    // Nieudane logowanie
    logActivity($conn, null, 'failed_login', $email);
    return ['success' => false, 'message' => 'Nieprawidłowy email lub hasło'];
}

// Wylogowanie użytkownika
function logoutUser() {
    if (isLoggedIn()) {
        $conn = getDBConnection();
        logActivity($conn, $_SESSION['user_id'], 'logout', 'success');
    }
    
    // Zniszcz sesję
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// Pobierz dane zalogowanego użytkownika
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("
        SELECT u.*, cd.* 
        FROM users u 
        LEFT JOIN company_data cd ON u.id = cd.user_id 
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Sprawdź timeout sesji
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        logoutUser();
        header('Location: /index.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}
?>