<?php

require 'includes/session_check.php';
require 'config/db.php';

$role = $_SESSION['role'] ?? 'student';

$student_id = $_SESSION['student_id'];

$first_name = $_SESSION['name'] ?? 'User';


try {

    $global_query = "
        SELECT

            COUNT(
                CASE
                    WHEN Status != 'Resolved'
                    THEN 1
                END
            ) AS total,

            SUM(
                CASE
                    WHEN Status = 'Submitted'
                    THEN 1
                    ELSE 0
                END
            ) AS submitted,

            SUM(
                CASE
                    WHEN Status = 'In Progress'
                    THEN 1
                    ELSE 0
                END
            ) AS in_progress,

            SUM(
                CASE
                    WHEN Status = 'Resolved'
                    THEN 1
                    ELSE 0
                END
            ) AS resolved

        FROM maintenance_request
    ";


    $global_stats = $pdo
        ->query($global_query)
        ->fetch(PDO::FETCH_ASSOC);




    $my_requests = [];


    if ($role === 'student') {

        $personal_stmt = $pdo->prepare("
            SELECT

                RequestID,
                DateSubmitted,
                Description,
                Category,
                Priority,
                Status,
                Room_No,
                Dorm_name

            FROM maintenance_request

            WHERE Student_ID = ?

            ORDER BY DateSubmitted DESC

            LIMIT 5
        ");


        $personal_stmt->execute([
            $student_id
        ]);


        $my_requests =
            $personal_stmt->fetchAll(PDO::FETCH_ASSOC);
    }


} catch (PDOException $e) {

    die(
        "Database communication error: " .
        htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard — Dorm Management</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>


<body>


<?php include 'includes/navbar.php'; ?>


<div class="page">




    <div class="dashboard-header">

        <div>

            <h1>
                Hello,
                <?= htmlspecialchars($first_name) ?>!
            </h1>


            <?php if ($role === 'student'): ?>

                <p class="dashboard-subtitle">
                    View maintenance activity and manage your requests.
                </p>

            <?php else: ?>

                <p class="dashboard-subtitle">
                    Monitor maintenance activity across the dorm.
                </p>

            <?php endif; ?>

        </div>



        <?php if ($role === 'student'): ?>

            <a
                href="maintenance_submit.php"
                class="btn btn-primary dashboard-action"
            >
                + New Maintenance Request
            </a>

        <?php endif; ?>



        <?php if ($role === 'admin'): ?>

            <a
                href="admin_maintenance.php"
                class="btn btn-primary dashboard-action"
            >
                View All Requests
            </a>

        <?php endif; ?>

    </div>


    <div class="dashboard-section">

        <h2>
            Maintenance Overview
        </h2>


        <div class="card-grid">




            <div class="stat-card">

                <span class="stat-number">

                    <?= $global_stats['total'] ?? 0 ?>

                </span>


                <span class="stat-label">

                    Active Maintenance Requests

                </span>

            </div>


         

            <div class="stat-card">

                <span class="stat-number">

                    <?= $global_stats['submitted'] ?? 0 ?>

                </span>


                <span class="stat-label">

                    Submitted

                </span>

            </div>


        

            <div class="stat-card">

                <span class="stat-number">

                    <?= $global_stats['in_progress'] ?? 0 ?>

                </span>


                <span class="stat-label">

                    In Progress

                </span>

            </div>


            <div class="stat-card">

                <span class="stat-number">

                    <?= $global_stats['resolved'] ?? 0 ?>

                </span>


                <span class="stat-label">

                    Resolved

                </span>

            </div>


        </div>

    </div>


   

    <?php if ($role === 'student'): ?>


        <div class="dashboard-section">

            <h2>
                My Recent Requests
            </h2>


            <?php if (empty($my_requests)): ?>

                <p class="empty-state">

                    You haven't submitted any
                    maintenance requests yet.

                </p>


                <a
                    href="maintenance_submit.php"
                    class="btn btn-primary"
                    style="width:auto;"
                >
                    Submit Your First Request
                </a>


            <?php else: ?>


                <?php foreach ($my_requests as $r): ?>


                    <div class="dashboard-request">


                        <div class="dashboard-request-top">


                            <div class="request-header">


                                <span
                                    class="badge
                                    badge-priority-<?= strtolower(
                                        $r['Priority']
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $r['Priority']
                                    ) ?>

                                </span>


                                <span class="badge badge-category">

                                    <?= htmlspecialchars(
                                        $r['Category']
                                    ) ?>

                                </span>


                            </div>


                            <span
                                class="badge
                                badge-status-<?= strtolower(
                                    str_replace(
                                        ' ',
                                        '-',
                                        $r['Status']
                                    )
                                ) ?>"
                            >

                                <?= htmlspecialchars(
                                    $r['Status']
                                ) ?>

                            </span>


                        </div>


                        <p
                            class="dashboard-request-description"
                        >

                            <?= htmlspecialchars(
                                $r['Description']
                            ) ?>

                        </p>


                        <div
                            class="dashboard-request-meta"
                        >

                            <span>

                                Dorm:
                                <?= htmlspecialchars(
                                    $r['Dorm_name']
                                ) ?>

                            </span>


                            <span>

                                Room:
                                <?= htmlspecialchars(
                                    $r['Room_No']
                                ) ?>

                            </span>


                            <span>

                                <?= htmlspecialchars(
                                    date(
                                        'M j, Y g:i A',
                                        strtotime(
                                            $r['DateSubmitted']
                                        )
                                    )
                                ) ?>

                            </span>


                        </div>


                    </div>


                <?php endforeach; ?>


                <a
                    href="maintenance_list.php"
                    class="view-all"
                >
                    View All My Requests →
                </a>


            <?php endif; ?>


        </div>


    <?php endif; ?>




    <?php if ($role === 'admin'): ?>


        <div class="dashboard-section">


            <h2>
                Maintenance Management
            </h2>


            <p class="empty-state">

                Resolve maintenance issues and
                manage all submitted requests.

            </p>


            <a
                href="admin_maintenance.php"
                class="btn btn-primary"
                style="width:auto;"
            >

                View All Maintenance Requests

            </a>


        </div>


    <?php endif; ?>


</div>


</body>

</html>