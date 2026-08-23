<?php

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

$timeout = 30 * 60;

if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $timeout
) {
    session_unset();
    session_destroy();

    header('Location: login.php');
    exit;
}

$_SESSION['last_activity'] = time();


if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}