<?php
/**
 * admin/logout.php
 * ---------------------------------------------------------
 * Destroys the admin session and returns to the login page.
 * Not one of the 4 requested files, but included since the
 * nav bars in dashboard/products/orders link to it.
 * ---------------------------------------------------------
 */

require_once '../config/db.php';

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

header('Location: login.php');
exit;
