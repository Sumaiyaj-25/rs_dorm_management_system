<?php

session_start();

if (isset($_SESSION['student_id'])) {

    header('Location: dashboard.php');
    exit;

}

if (isset($_SESSION['staff_id'])) {

    header('Location: admin_maintenance.php');
    exit;

}

header('Location: login.php');
exit;
