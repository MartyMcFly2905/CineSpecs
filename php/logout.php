<?php
// Logout e redirect alla home
session_start();

$_SESSION = [];

// Cancella anche il cookie di sessione
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: ../index.php');
exit;
