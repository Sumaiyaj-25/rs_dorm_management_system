<?php

require 'includes/session_check.php';
require 'config/db.php';

$role = $_SESSION['role'] ?? 'student';
$first_name = $_SESSION['name'] ?? 'User';
$student_id = $_SESSION['student_id'] ?? 0;

$requests = [];
$parcel_count = 0;
$laundry_count = 0;
$leave_count = 0;
$mood = null;

if ($role === 'student' && $student_id) {

    $stmt = $pdo->prepare(
        'SELECT
            RequestID,
            Description,
            Category,
            Priority,
            Status,
            Room_No,
            Dorm_name,
            DateSubmitted
         FROM Maintenance_request
         WHERE Student_ID = ?
         ORDER BY DateSubmitted DESC
         LIMIT 3'
    );

    $stmt->execute([$student_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);



    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM parcel
         WHERE Student_ID = ?
         AND Status != "Collected"'
    );

    $stmt->execute([$student_id]);
    $parcel_count = (int) $stmt->fetchColumn();



    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM laundry
         WHERE owned_by = ?
         AND laundry_status != "Returned"'
    );

    $stmt->execute([$student_id]);
    $laundry_count = (int) $stmt->fetchColumn();


    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM Leave_Request
         WHERE Student_ID = ?
         AND Status = "Pending"'
    );

    $stmt->execute([$student_id]);
    $leave_count = (int) $stmt->fetchColumn();


    $stmt = $pdo->prepare(
        'SELECT mood
         FROM mood_log
         WHERE Student_ID = ?
         ORDER BY created_at DESC
         LIMIT 1'
    );

    try {
        $stmt->execute([$student_id]);
        $mood = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $mood = null;
    }
}

$admin_counts = [
    'maintenance' => 0,
    'transfers' => 0,
    'visitors_today' => 0,
    'visitors_inside' => 0,
    'sos' => 0,
    'leave' => 0,
    'parcels' => 0,
    'laundry' => 0
];


if ($role === 'admin') {


    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM maintenance_request
         WHERE Status IN ('Submitted', 'In Progress')"
    );

    $admin_counts['maintenance'] =
        (int) $stmt->fetchColumn();

    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM room_transfer_request
         WHERE Status = 'Pending'"
    );

    $admin_counts['transfers'] =
        (int) $stmt->fetchColumn();

    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM Visitor
         WHERE Visit_Date = CURDATE()"
    );

    $admin_counts['visitors_today'] =
        (int) $stmt->fetchColumn();


    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM Visitor
         WHERE Status = 'Inside'"
    );

    $admin_counts['visitors_inside'] =
        (int) $stmt->fetchColumn();


    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM SOS_Request
         WHERE Status IN ('Pending', 'In Progress')"
    );

    $admin_counts['sos'] =
        (int) $stmt->fetchColumn();

    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM Leave_Request
         WHERE Status = 'Pending'"
    );

    $admin_counts['leave'] =
        (int) $stmt->fetchColumn();


    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM parcel
         WHERE Status != 'Collected'"
    );

    $admin_counts['parcels'] =
        (int) $stmt->fetchColumn();

    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM laundry
         WHERE laundry_status != 'Returned'"
    );

    $admin_counts['laundry'] =
        (int) $stmt->fetchColumn();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Dashboard - Dorm Management
</title>

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

<?php if ($role === 'admin'): ?>

<div class="dashboard-header">

    <div>

        <h1>
            Hello, <?= htmlspecialchars($first_name) ?>!
        </h1>

        <p class="dashboard-subtitle">
            Welcome to the Dorm Management admin dashboard.
        </p>

    </div>

</div>


<h2>Management Overview</h2>


<div class="card-grid">
    <div class="stat-card">

        <span class="stat-number">
            <?= $admin_counts['parcels'] ?>
        </span>

        <span class="stat-label">
            Pending Parcels
        </span>

        <a
            href="admin_parcel_list.php"
            class="btn btn-secondary"
        >
            Manage Parcels
        </a>

    </div>


    <div class="stat-card">

        <span class="stat-number">
            <?= $admin_counts['leave'] ?>
        </span>

        <span class="stat-label">
            Pending Leave Requests
        </span>

        <a
            href="admin_leave.php"
            class="btn btn-secondary"
        >
            Manage Leave
        </a>

    </div>

    <div class="stat-card">

        <span class="stat-number">
            <?= $admin_counts['maintenance'] ?>
        </span>

        <span class="stat-label">
            Open Maintenance Tickets
        </span>

        <a
            href="admin_maintenance.php"
            class="btn btn-secondary"
        >
            Manage Maintenance
        </a>

    </div>
    <div class="stat-card">

        <span class="stat-number">
            —
        </span>

        <span class="stat-label">
            Wellbeing Alert Monitor
        </span>

        <a
            href="counselor_dashboard.php"
            class="btn btn-secondary"
        >
            View Alerts
        </a>

    </div>


    <div class="stat-card">

        <span class="stat-number">
            <?= $admin_counts['visitors_today'] ?>
        </span>

        <span class="stat-label">
            Recent Visitor Activity Today
        </span>

        <p class="empty-state">
            <?= $admin_counts['visitors_inside'] ?>
            currently inside
        </p>

        <a
            href="admin_visitor.php"
            class="btn btn-secondary"
        >
            Visitor QR
        </a>

    </div>


    <div class="stat-card">

        <span class="stat-number">
            <?= $admin_counts['laundry'] ?>
        </span>

        <span class="stat-label">
            Active Laundry Requests
        </span>

        <a
            href="admin_laundry.php"
            class="btn btn-secondary"
        >
            Manage Laundry
        </a>

    </div>


    <div class="stat-card">

        <span class="stat-number">
            <?= $admin_counts['transfers'] ?>
        </span>

        <span class="stat-label">
            Pending Room Transfers
        </span>

        <a
            href="admin_room_transfer.php"
            class="btn btn-secondary"
        >
            Review Transfers
        </a>

    </div>


    <div class="stat-card stat-warning">

        <span class="stat-number">
            <?= $admin_counts['sos'] ?>
        </span>

        <span class="stat-label">
            Emergency Alerts
        </span>

        <a
            href="admin_sos.php"
            class="btn btn-secondary"
        >
            Manage SOS
        </a>

    </div>

</div>


<hr>


<h2>Quick Access</h2>

<div class="card-grid">


    <div class="stat-card">

        <span class="stat-label">
            Parcel Management
        </span>

        <a
            href="admin_parcel_list.php"
            class="btn btn-primary"
        >
            Open
        </a>

    </div>


    <div class="stat-card">

        <span class="stat-label">
            Leave Management
        </span>

        <a
            href="admin_leave.php"
            class="btn btn-primary"
        >
            Open
        </a>

    </div>


    <div class="stat-card">

        <span class="stat-label">
            Maintenance Management
        </span>

        <a
            href="admin_maintenance.php"
            class="btn btn-primary"
        >
            Open
        </a>

    </div>


    <div class="stat-card">

        <span class="stat-label">
            Laundry Management
        </span>

        <a
            href="admin_laundry.php"
            class="btn btn-primary"
        >
            Open
        </a>

    </div>


    <div class="stat-card">

        <span class="stat-label">
            Room Assistance
        </span>

        <a
            href="admin_room_assignment.php"
            class="btn btn-primary"
        >
            Open
        </a>

    </div>


    <div class="stat-card">

        <span class="stat-label">
            Visitor Security
        </span>

        <a
            href="admin_visitor.php"
            class="btn btn-primary"
        >
            Open
        </a>

    </div>

</div>
```

<?php else: ?>


<div class="dashboard-header">

    <div>

        <h1>
            Hello, <?= htmlspecialchars($first_name) ?>!
        </h1>

        <p class="dashboard-subtitle">
            Welcome to the Dorm Management system.
        </p>

    </div>

</div>


<div class="request-card">

    <div class="request-header">

        <h3>
            Emergency SOS
        </h3>

    </div>

    <p class="request-desc">
        Need urgent help from dorm staff?
        Send an SOS request immediately.
    </p>

    <div class="action-row">

        <a
            href="my_sos.php"
            class="btn btn-primary"
        >
            Send SOS Request
        </a>

    </div>

</div>


<hr>


<h2>
    Current Maintenance Updates
</h2>


<?php if (empty($requests)): ?>

    <p class="empty-state">
        You have no maintenance requests yet.
    </p>

<?php else: ?>

    <div class="request-list">

        <?php foreach ($requests as $request): ?>

            <div
                class="request-card priority-<?= strtolower($request['Priority']) ?>"
            >

                <div class="request-header">

                    <span
                        class="badge badge-priority-<?= strtolower($request['Priority']) ?>"
                    >
                        <?= htmlspecialchars($request['Priority']) ?>
                    </span>

                    <span class="badge badge-category">
                        <?= htmlspecialchars($request['Category']) ?>
                    </span>

                    <span
                        class="badge badge-status-<?= strtolower(
                            str_replace(
                                ' ',
                                '-',
                                $request['Status']
                            )
                        ) ?>"
                    >
                        <?= htmlspecialchars($request['Status']) ?>
                    </span>

                </div>


                <p class="request-desc">

                    <?= htmlspecialchars(
                        $request['Description']
                    ) ?>

                </p>


                <div class="request-meta">

                    <span>
                        Room:
                        <?= htmlspecialchars($request['Room_No']) ?>
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

    <a
        href="maintenance_submit.php"
        class="btn btn-primary"
    >
        Submit Maintenance Request
    </a>

    <a
        href="maintenance_list.php"
        class="btn btn-secondary"
    >
        View All Requests
    </a>

</div>


<hr>


<h2>
    Parcel Status
</h2>

<div class="request-card">

    <div class="request-header">

        <h3>
            My Parcels
        </h3>

        <span class="badge">
            <?= $parcel_count ?>
            Pending
        </span>

    </div>

    <p class="request-desc">

        <?php if ($parcel_count > 0): ?>

            You have parcel(s) waiting for collection.

        <?php else: ?>

            You have no parcels waiting for collection.

        <?php endif; ?>

    </p>

    <div class="action-row">

        <a
            href="parcel_list.php"
            class="btn btn-primary"
        >
            View My Parcels
        </a>

    </div>

</div>


<hr>

<h2>
    Laundry Status
</h2>

<div class="request-card">

    <div class="request-header">

        <h3>
            My Laundry
        </h3>

        <span class="badge">
            <?= $laundry_count ?>
            Active
        </span>

    </div>

    <p class="request-desc">

        <?php if ($laundry_count > 0): ?>

            You have active laundry request(s).

        <?php else: ?>

            You have no active laundry requests.

        <?php endif; ?>

    </p>

    <div class="action-row">

        <a
            href="laundry_request.php"
            class="btn btn-primary"
        >
            Laundry Request
        </a>

    </div>

</div>


<hr>


<h2>
    Leave Request Summary
</h2>

<div class="request-card">

    <div class="request-header">

        <h3>
            Leave Requests
        </h3>

        <span class="badge">
            <?= $leave_count ?>
            Pending
        </span>

    </div>

    <p class="request-desc">

        <?php if ($leave_count > 0): ?>

            You have pending leave request(s).

        <?php else: ?>

            You have no pending leave requests.

        <?php endif; ?>

    </p>

    <div class="action-row">

        <a
            href="my_leaves.php"
            class="btn btn-primary"
        >
            View Leave Requests
        </a>

    </div>

</div>


<hr>

<h2>
    Meal Information
</h2>

<div class="request-card">

    <div class="request-header">

        <h3>
            Today's Meal Information
        </h3>

    </div>

    <p class="request-desc">
        Check the available meal information and meal schedule.
    </p>

    <div class="action-row">

        <a
            href="meals.php"
            class="btn btn-primary"
        >
            View Meal Information
        </a>

    </div>

</div>


<hr>

<h2>
    Mood Check / My Mood
</h2>

<div class="request-card">

    <div class="request-header">

        <h3>
            My Mood
        </h3>

        <?php if ($mood !== null && $mood !== false): ?>

            <span class="badge">
                <?= htmlspecialchars($mood) ?>
            </span>

        <?php else: ?>

            <span class="badge">
                Not logged
            </span>

        <?php endif; ?>

    </div>

    <p class="request-desc">

        <?php if ($mood !== null && $mood !== false): ?>

            Your latest mood has been recorded.

        <?php else: ?>

            You have not logged your mood yet.

        <?php endif; ?>

    </p>

    <div class="action-row">

        <a
            href="mood_log.php"
            class="btn btn-primary"
        >
            Check My Mood
        </a>

    </div>

</div>


<hr>

<h2>
    Recent Notifications
</h2>

<div class="request-card">

    <div class="request-header">

        <h3>
            Notifications
        </h3>

    </div>

    <p class="request-desc">
        Check your latest updates and requests from the dorm management system.
    </p>

    <div class="action-row">

        <a
            href="maintenance_list.php"
            class="btn btn-secondary"
        >
            View Updates
        </a>

    </div>

</div>

<?php endif; ?>

</div>

</body>
</html>
