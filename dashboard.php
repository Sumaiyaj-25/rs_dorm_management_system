<?php
require 'includes/session_check.php';
require 'config/db.php';

$role = $_SESSION['role'] ?? 'student';
$first_name = $_SESSION['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="page">
        <div class="dashboard-header">
            <div>
                <h1>Hello, <?= htmlspecialchars($first_name) ?>!</h1>
                <p class="dashboard-subtitle">Welcome to the Dorm Management system.</p>
            </div>
        </div>
    </div>
</body>
</html>