<?php

require 'includes/session_check.php';
require 'config/db.php';

$role = $_SESSION['role'] ?? 'student';
$first_name = $_SESSION['name'] ?? 'User';
$student_id = $_SESSION['student_id'] ?? 0;

$requests = [];
$latest_transfer = null;
$best_roommate = null;
$visitors = [];

if ($role === 'student') {

    require 'includes/roommate_match.php';

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

    if ($student_id) {

        $stmt = $pdo->prepare(
            'SELECT
                Current_Room,
                Requested_Room,
                Reason,
                Status,
                DateRequested
             FROM room_transfer_request
             WHERE Student_ID = ?
             ORDER BY DateRequested DESC
             LIMIT 1'
        );

        $stmt->execute([$student_id]);
        $latest_transfer = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($student_id) {

        $stmt = $pdo->prepare(
            'SELECT
                Cleanliness,
                NoiseTolerance,
                StudyHabit,
                SleepingHabit
             FROM preferences
             WHERE Student_ID = ?'
        );

        $stmt->execute([$student_id]);
        $my_pref = $stmt->fetch(PDO::FETCH_ASSOC);


        $stmt = $pdo->prepare(
            'SELECT Gender
             FROM student
             WHERE Student_ID = ?'
        );

        $stmt->execute([$student_id]);
        $my_student = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($my_pref && $my_student) {

            $stmt = $pdo->prepare(
                'SELECT
                    s.Student_ID,
                    s.FirstName,
                    s.LastName,
                    s.Department,
                    s.Gender,
                    p.Cleanliness,
                    p.NoiseTolerance,
                    p.StudyHabit,
                    p.SleepingHabit
                 FROM student s
                 JOIN preferences p
                   ON p.Student_ID = s.Student_ID
                 WHERE s.Student_ID <> ?
                   AND s.Gender = ?
                   AND NOT EXISTS (
                        SELECT 1
                        FROM compatible m
                        WHERE
                            (
                                (m.Requesting_Student_ID = ?
                                 AND m.Potential_Roommate_ID = s.Student_ID)
                                OR
                                (m.Requesting_Student_ID = s.Student_ID
                                 AND m.Potential_Roommate_ID = ?)
                            )
                            AND m.Status IN ("Pending", "Accepted")
                   )'
            );

            $stmt->execute([
                $student_id,
                $my_student['Gender'],
                $student_id,
                $student_id
            ]);

            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);


            foreach ($candidates as &$candidate) {

                $candidate['Score'] =
                    calculateCompatibility(
                        $my_pref,
                        $candidate
                    );
            }

            unset($candidate);


            usort(
                $candidates,
                function ($a, $b) {
                    return $b['Score'] <=> $a['Score'];
                }
            );


            if (!empty($candidates)) {
                $best_roommate = $candidates[0];
            }
        }
    }

    if ($student_id) {

        $stmt = $pdo->prepare(
            'SELECT
                Visitor_ID,
                Visitor_Name,
                Visitor_Phone,
                Visit_Date,
                Status,
                Entry_Time,
                Exit_Time
             FROM Visitor
             WHERE Student_ID = ?
             ORDER BY Visitor_ID DESC
             LIMIT 3'
        );

        $stmt->execute([$student_id]);
        $visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <div class="request-card">

        <div class="request-header">

            <h3>
                Maintenance
            </h3>

            <span class="badge">
                <?= $admin_counts['maintenance'] ?>
                Active
            </span>

        </div>

        <p class="request-desc">
            Maintenance requests that are submitted or currently in progress.
        </p>

        <div class="action-row">

            <a
                href="admin_maintenance.php"
                class="btn btn-primary"
            >
                Manage Maintenance
            </a>

        </div>

    </div>

    <div class="request-card">

        <div class="request-header">

            <h3>
                Room Transfers
            </h3>

            <span class="badge">
                <?= $admin_counts['transfers'] ?>
                Pending
            </span>

        </div>

        <p class="request-desc">
            Review student room transfer requests and approve or reject them.
        </p>

        <div class="action-row">

            <a
                href="admin_room_transfer.php"
                class="btn btn-primary"
            >
                Manage Room Transfers
            </a>

        </div>

    </div>

    <div class="request-card">

        <div class="request-header">

            <h3>
                Visitor Checking
            </h3>

            <span class="badge">
                <?= $admin_counts['visitors_today'] ?>
                Today
            </span>

        </div>

        <p class="request-desc">

            <?= $admin_counts['visitors_inside'] ?>
            visitor(s) currently inside the dorm.

        </p>

        <div class="action-row">

            <a
                href="admin_visitor.php"
                class="btn btn-primary"
            >
                Visitor QR Checking
            </a>

        </div>

    </div>

    <div class="request-card">

        <div class="request-header">

            <h3>
                Emergency Assistance
            </h3>

            <span class="badge">
                <?= $admin_counts['sos'] ?>
                Active
            </span>

        </div>

        <p class="request-desc">
            View and respond to active student SOS requests.
        </p>

        <div class="action-row">

            <a
                href="admin_sos.php"
                class="btn btn-primary"
            >
                Manage SOS Requests
            </a>

        </div>

    </div>

    <div class="request-card">

        <div class="request-header">

            <h3>
                Leave Requests
            </h3>

            <span class="badge">
                <?= $admin_counts['leave'] ?>
                Pending
            </span>

        </div>

        <p class="request-desc">
            Review and manage student leave requests.
        </p>

        <div class="action-row">

            <a
                href="admin_leave.php"
                class="btn btn-primary"
            >
                Manage Leave Requests
            </a>

        </div>

    </div>

    <div class="request-card">

        <div class="request-header">

            <h3>
                Parcels
            </h3>

            <span class="badge">
                <?= $admin_counts['parcels'] ?>
                Awaiting Collection
            </span>

        </div>

        <p class="request-desc">
            View parcels that have not yet been collected by students.
        </p>

        <div class="action-row">

            <a
                href="admin_parcel_list.php"
                class="btn btn-primary"
            >
                Manage Parcels
            </a>

        </div>

    </div>

    <div class="request-card">

        <div class="request-header">

            <h3>
                Laundry
            </h3>

            <span class="badge">
                <?= $admin_counts['laundry'] ?>
                Active
            </span>

        </div>

        <p class="request-desc">
            Manage student laundry requests and payment status.
        </p>

        <div class="action-row">

            <a
                href="admin_laundry.php"
                class="btn btn-primary"
            >
                Manage Laundry
            </a>

        </div>

    </div>

    <div class="request-card">

        <div class="request-header">

            <h3>
                Room Assignment
            </h3>

        </div>

        <p class="request-desc">
            Manage student room assignments.
        </p>

        <div class="action-row">

            <a
                href="admin_room_assignment.php"
                class="btn btn-primary"
            >
                Manage Room Assignment
            </a>

        </div>

    </div>


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


    <h2>Current Maintenance Updates</h2>


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

    <h2>Room Transfer</h2>


    <?php if ($latest_transfer): ?>

        <div class="request-card">

            <div class="request-header">

                <span>
                    Current Room:
                    <?= htmlspecialchars(
                        $latest_transfer['Current_Room']
                    ) ?>
                </span>

                <span>
                    Requested Room:
                    <?= htmlspecialchars(
                        $latest_transfer['Requested_Room']
                    ) ?>
                </span>

                <span class="badge">
                    <?= htmlspecialchars(
                        $latest_transfer['Status']
                    ) ?>
                </span>

            </div>


            <p class="request-desc">
                <?= htmlspecialchars(
                    $latest_transfer['Reason']
                ) ?>
            </p>


            <div class="request-meta">

                <span>
                    Requested:
                    <?= htmlspecialchars(
                        $latest_transfer['DateRequested']
                    ) ?>
                </span>

            </div>

        </div>

    <?php else: ?>

        <p class="empty-state">
            You have no room transfer requests.
        </p>

    <?php endif; ?>


    <div class="action-row">

        <a
            href="room_transfer.php"
            class="btn btn-primary"
        >
            Room Transfer
        </a>

    </div>


    <hr>

    <h2>Find a Roommate</h2>


    <?php if ($best_roommate): ?>

        <div class="request-card">

            <div class="request-header">

                <span class="badge">
                    <?= htmlspecialchars(
                        $best_roommate['Score']
                    ) ?>% Match
                </span>

            </div>


            <p class="request-desc">

                <strong>
                    <?= htmlspecialchars(
                        $best_roommate['FirstName'] .
                        ' ' .
                        $best_roommate['LastName']
                    ) ?>
                </strong>

            </p>


            <div class="request-meta">

                <span>
                    Department:
                    <?= htmlspecialchars(
                        $best_roommate['Department']
                    ) ?>
                </span>

            </div>

        </div>

    <?php elseif ($my_pref ?? null): ?>

        <p class="empty-state">
            No compatible roommates are currently available.
        </p>

    <?php else: ?>

        <p class="empty-state">
            Set your roommate preferences to find a match.
        </p>

    <?php endif; ?>


    <div class="action-row">

        <a
            href="recommend.php"
            class="btn btn-primary"
        >
            Find a Roommate
        </a>

    </div>


    <hr>

    <h2>My Visitors</h2>


    <?php if (empty($visitors)): ?>

        <p class="empty-state">
            You have no registered visitors.
        </p>

    <?php else: ?>

        <div class="request-list">

            <?php foreach ($visitors as $visitor): ?>

                <div class="request-card">

                    <div class="request-header">

                        <strong>
                            <?= htmlspecialchars(
                                $visitor['Visitor_Name']
                            ) ?>
                        </strong>

                        <span class="badge">
                            <?= htmlspecialchars(
                                $visitor['Status']
                            ) ?>
                        </span>

                    </div>


                    <div class="request-meta">

                        <span>
                            Visit Date:
                            <?= htmlspecialchars(
                                $visitor['Visit_Date']
                            ) ?>
                        </span>


                        <?php if (!empty($visitor['Entry_Time'])): ?>

                            <span>
                                Entry:
                                <?= htmlspecialchars(
                                    $visitor['Entry_Time']
                                ) ?>
                            </span>

                        <?php endif; ?>


                        <?php if (!empty($visitor['Exit_Time'])): ?>

                            <span>
                                Exit:
                                <?= htmlspecialchars(
                                    $visitor['Exit_Time']
                                ) ?>
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <div class="action-row">

        <a
            href="visitor_list.php"
            class="btn btn-primary"
        >
            My Visitor
        </a>

        <a
            href="visitor_register.php"
            class="btn btn-secondary"
        >
            Register Visitor
        </a>

    </div>


    <hr>

    <h2>Emergency Assistance (SOS)</h2>


    <p>
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


<?php endif; ?>


</div>

</body>
</html>