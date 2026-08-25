<?php

require 'includes/session_check.php';
require 'config/db.php';


if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: dashboard.php');
    exit;
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $request_id = intval($_POST['request_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';

    $allowed_statuses = [
        'In Progress',
        'Resolved'
    ];

    if (
        $request_id > 0 &&
        in_array($new_status, $allowed_statuses, true)
    ) {

        $update = $pdo->prepare("
            UPDATE maintenance_request
            SET Status = ?
            WHERE RequestID = ?
        ");

        $update->execute([
            $new_status,
            $request_id
        ]);
    }

    header('Location: admin_maintenance.php');
    exit;
}




$stmt = $pdo->query("
    SELECT

        m.RequestID,
        m.DateSubmitted,
        m.Description,
        m.Photo,
        m.Category,
        m.Priority,
        m.Status,
        m.Dorm_name,
        m.Room_No,

        s.Student_ID,
        s.FirstName,
        s.LastName,
        s.Email

    FROM maintenance_request m

    JOIN student s
        ON m.Student_ID = s.Student_ID

    ORDER BY

        CASE
            WHEN m.Status = 'Submitted' THEN 1
            WHEN m.Status = 'In Progress' THEN 2
            WHEN m.Status = 'Resolved' THEN 3
        END,

        FIELD(
            m.Priority,
            'High',
            'Medium',
            'Low'
        ),

        m.DateSubmitted DESC
");

$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Maintenance Management</title>

<link rel="stylesheet"
      href="assets/css/style.css">

</head>


<body>


<?php include 'includes/navbar.php'; ?>


<div class="page">




    <h1>
        Maintenance Management
    </h1>

    <p class="subtitle">
        View and manage all maintenance requests.
    </p>


    <?php if (empty($requests)): ?>


        <p class="empty-state">
            No maintenance requests have been submitted.
        </p>


    <?php else: ?>


        <div class="request-list">


            <?php foreach ($requests as $r): ?>


                <div class="request-card
                    priority-<?= strtolower($r['Priority']) ?>">



                    <div class="request-header">


                        <span>
                            Request
                            #<?= htmlspecialchars(
                                $r['RequestID']
                            ) ?>
                        </span>


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


                    <p class="request-desc">

                        <?= nl2br(
                            htmlspecialchars(
                                $r['Description']
                            )
                        ) ?>

                    </p>


                    <!-- =================================
                         STUDENT + ROOM INFORMATION
                    ================================== -->

                    <div class="request-meta">


                        <span>

                            Student:
                            <?= htmlspecialchars(
                                $r['FirstName'] .
                                ' ' .
                                $r['LastName']
                            ) ?>

                        </span>


                        <span>

                            Student ID:
                            <?= htmlspecialchars(
                                $r['Student_ID']
                            ) ?>

                        </span>


                        <span>

                            Email:
                            <?= htmlspecialchars(
                                $r['Email']
                            ) ?>

                        </span>


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

                            Submitted:
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


                    

                    <?php if ($r['Photo']): ?>


                        <img
                            class="request-photo"
                            src="<?= htmlspecialchars(
                                $r['Photo']
                            ) ?>"
                            alt="Maintenance issue photo"
                        >


                    <?php endif; ?>





                    <?php if (
                        $r['Status'] === 'Submitted'
                    ): ?>


                        <form
                            method="POST"
                            style="margin-top: 15px;"
                        >

                            <input
                                type="hidden"
                                name="request_id"
                                value="<?= htmlspecialchars(
                                    $r['RequestID']
                                ) ?>"
                            >

                            <input
                                type="hidden"
                                name="new_status"
                                value="In Progress"
                            >

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Start Work
                            </button>

                        </form>


                    <?php elseif (
                        $r['Status'] === 'In Progress'
                    ): ?>



                        <form
                            method="POST"
                            style="margin-top: 15px;"
                        >

                            <input
                                type="hidden"
                                name="request_id"
                                value="<?= htmlspecialchars(
                                    $r['RequestID']
                                ) ?>"
                            >

                            <input
                                type="hidden"
                                name="new_status"
                                value="Resolved"
                            >

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                ✓ Mark as Resolved
                            </button>

                        </form>


                    <?php elseif (
                        $r['Status'] === 'Resolved'
                    ): ?>


                        
                        <p
                            style="
                                margin-top:15px;
                                color:var(--low);
                                font-size:13px;
                                font-weight:600;
                            "
                        >

                            ✓ This issue has been resolved.

                        </p>


                    <?php endif; ?>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>


</body>

</html>