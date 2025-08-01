<?php
// config/database.php

// Konfiguracja bazy danych
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'client_panel');

// Funkcja do nawiązania połączenia z bazą danych
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Sprawdzenie połączenia
    if ($conn->connect_error) {
        // W produkcji nie pokazuj szczegółów błędu
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            die("Connection failed: " . $conn->connect_error);
        } else {
            die("Błąd połączenia z bazą danych. Skontaktuj się z administratorem.");
        }
    }
    
    // Ustawienie kodowania
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Funkcja do bezpiecznego wykonywania zapytań z prepared statements
function executeQuery($conn, $sql, $types = "", $params = []) {
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            die("Prepare failed: " . $conn->error);
        } else {
            return false;
        }
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    
    if ($result === false) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            die("Execute failed: " . $stmt->error);
        } else {
            return false;
        }
    }
    
    // Zwróć wynik w zależności od typu zapytania
    if (strpos(strtoupper($sql), 'SELECT') === 0) {
        return $stmt->get_result();
    } else {
        return $result;
    }
}

// Funkcja do rozpoczęcia transakcji
function beginTransaction($conn) {
    $conn->autocommit(false);
    $conn->begin_transaction();
}

// Funkcja do zatwierdzenia transakcji
function commitTransaction($conn) {
    $conn->commit();
    $conn->autocommit(true);
}

// Funkcja do wycofania transakcji
function rollbackTransaction($conn) {
    $conn->rollback();
    $conn->autocommit(true);
}

// Funkcja do pobierania pojedynczego rekordu
function fetchSingle($conn, $sql, $types = "", $params = []) {
    $result = executeQuery($conn, $sql, $types, $params);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Funkcja do pobierania wszystkich rekordów
function fetchAll($conn, $sql, $types = "", $params = []) {
    $result = executeQuery($conn, $sql, $types, $params);
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Funkcja do sprawdzania czy rekord istnieje
function recordExists($conn, $table, $column, $value) {
    $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = ?";
    $result = fetchSingle($conn, $sql, "s", [$value]);
    return $result['count'] > 0;
}

// Funkcja do generowania unikalnego numeru
function generateUniqueNumber($conn, $table, $column, $prefix) {
    do {
        $number = $prefix . date('Y') . sprintf('%06d', rand(1, 999999));
        $exists = recordExists($conn, $table, $column, $number);
    } while ($exists);
    
    return $number;
}

// Funkcja do cachowania zapytań
$queryCache = [];

function cachedQuery($conn, $sql, $types = "", $params = [], $cacheTime = 300) {
    $cacheKey = md5($sql . serialize($params));
    
    if (isset($queryCache[$cacheKey]) && $queryCache[$cacheKey]['expires'] > time()) {
        return $queryCache[$cacheKey]['data'];
    }
    
    $result = fetchAll($conn, $sql, $types, $params);
    
    $queryCache[$cacheKey] = [
        'data' => $result,
        'expires' => time() + $cacheTime
    ];
    
    return $result;
}

// Funkcja do czyszczenia cache
function clearQueryCache() {
    global $queryCache;
    $queryCache = [];
}

// Stałe konfiguracyjne
define('DEBUG_MODE', false); // Zmień na true podczas developmentu
define('SESSION_LIFETIME', 3600); // 1 godzina
define('UPLOAD_PATH', '../assets/uploads/');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minut