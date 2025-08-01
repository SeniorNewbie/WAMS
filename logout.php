<?php
// logout.php - Wylogowanie użytkownika
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'config/session.php';

initSession();

// Wyloguj użytkownika
logoutUser();

// Przekieruj do strony logowania
header('Location: index.php?logout=1');
exit();
?>