<?php
require 'includes/session_check.php';
require 'config/db.php';

$role = $_SESSION['role'] ?? 'student';
$first_name = $_SESSION['name'] ?? 'User';
$student_id = $_SESSION['student_id'] ?? 0;

$requests = [];

if ($student_id) {
    $stmt = $pdo->prepare(
        'SELECT RequestID, Description, Category, Priority, Status, Room_No, Dorm_name, DateSubmitted
         FROM Maintenance_request
         WHERE Student_ID = ?
         ORDER BY DateSubmitted DESC'
    );

    $stmt->execute([$student_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="page">

        <div class="dashboard-header">
            <div>
                <h1>Hello, <?= htmlspecialchars($first_name) ?>!</h1>
                <p class="dashboard-subtitle">
                    Welcome to the Dorm Management system.
                </p>
            </div>
        </div>

        <h2>Current Maintenance Updates</h2>

        <?php if (empty($requests)): ?>

            <p class="empty-state">
                You have no maintenance requests yet.
            </p>

        <?php else: ?>

            <div class="request-list">

                <?php foreach ($requests as $request): ?>

                    <div class="request-card priority-<?= strtolower($request['Priority']) ?>">

                        <div class="request-header">

                            <span class="badge badge-priority-<?= strtolower($request['Priority']) ?>">
                                <?= htmlspecialchars($request['Priority']) ?>
                            </span>

                            <span class="badge badge-category">
                                <?= htmlspecialchars($request['Category']) ?>
                            </span>

                            <span class="badge badge-status-<?= strtolower(str_replace(' ', '-', $request['Status'])) ?>">
                                <?= htmlspecialchars($request['Status']) ?>
                            </span>

                        </div>

                        <p class="request-desc">
                            <?= htmlspecialchars($request['Description']) ?>
                        </p>

                        <div class="request-meta">
                            <span>
                                Room: <?= htmlspecialchars($request['Room_No']) ?>
                            </span>

                            <span>
                                <?= htmlspecialchars($request['Dorm_name']) ?>
                            </span>

                            <span>
                                <?= htmlspecialchars($request['DateSubmitted']) ?>
                            </span>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <div class="action-row">

            <a href="maintenance_submit.php" class="btn btn-primary">
                Submit Maintenance Request
            </a>

            <a href="maintenance_list.php" class="btn btn-secondary">
                View All Requests
            </a>

        </div>

        <hr>

<h2>Emergency Assistance (SOS)</h2>

<p>
    Need urgent help from dorm staff?
    Send an SOS request immediately.
</p>

<div class="action-row">

    <a href="my_sos.php" class="btn btn-danger">
        Send SOS Request
    </a>

</div>

    </div>

</body>
</html>
