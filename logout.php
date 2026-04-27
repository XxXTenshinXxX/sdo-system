<?php
session_start();
require_once __DIR__ . '/includes/user-activity.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    userActivityLogEvent('logout', 'Logged out from main system');
    userActivityMarkUserOffline();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

header('Location: login.php');
exit;
