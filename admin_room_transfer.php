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


$message = '';
$error = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $transfer_id = intval($_POST['transfer_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';


    if (
        $transfer_id <= 0 ||
        !in_array($new_status, ['Approved', 'Rejected'], true)
    ) {

        $error = 'Invalid transfer request.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT
                Transfer_ID,
                Student_ID,
                Current_Room,
                Requested_Room,
                Status
             FROM room_transfer_request
             WHERE Transfer_ID = ?'
        );

        $stmt->execute([$transfer_id]);

        $transfer = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$transfer) {

            $error = 'Transfer request not found.';

        } elseif ($transfer['Status'] !== 'Pending') {

            $error = 'This transfer request has already been processed.';

        } else {



            if ($new_status === 'Approved') {


                /* Check requested room */

                $stmt = $pdo->prepare(
                    'SELECT Capacity
                     FROM Room
                     WHERE Room_No = ?
                     AND Status = "Active"'
                );

                $stmt->execute([
                    $transfer['Requested_Room']
                ]);

                $room = $stmt->fetch(PDO::FETCH_ASSOC);


                if (!$room) {

                    $error = 'The requested room is no longer available.';

                } else {


                    /* Check current occupancy */

                    $stmt = $pdo->prepare(
                        'SELECT COUNT(*)
                         FROM Student
                         WHERE Room_No = ?'
                    );

                    $stmt->execute([
                        $transfer['Requested_Room']
                    ]);

                    $occupancy = (int) $stmt->fetchColumn();


                    if ($occupancy >= (int) $room['Capacity']) {

                        $error = 'The requested room is already full.';

                    } else {


                        /*
                        Update student's room
                        */

                        $stmt = $pdo->prepare(
                            'UPDATE Student
                             SET Room_No = ?
                             WHERE Student_ID = ?'
                        );

                        $stmt->execute([
                            $transfer['Requested_Room'],
                            $transfer['Student_ID']
                        ]);


                        /*
                        Update transfer request status
                        */

                        $stmt = $pdo->prepare(
                            'UPDATE room_transfer_request
                             SET Status = "Approved"
                             WHERE Transfer_ID = ?'
                        );

                        $stmt->execute([
                            $transfer_id
                        ]);


                        $message = 'Room transfer approved successfully.';
                    }
                }

            } else {

                $stmt = $pdo->prepare(
                    'UPDATE room_transfer_request
                     SET Status = "Rejected"
                     WHERE Transfer_ID = ?'
                );

                $stmt->execute([
                    $transfer_id
                ]);

                $message = 'Room transfer request rejected.';
            }
        }
    }
}


/* =========================================================
   GET ALL TRANSFER REQUESTS
========================================================= */

$stmt = $pdo->query(
    'SELECT
        r.Transfer_ID,
        r.Student_ID,
        r.Current_Room,
        r.Requested_Room,
        r.Reason,
        r.Status,
        r.DateRequested,

        s.FirstName,
        s.LastName,
        s.Email,
        s.Gender

     FROM room_transfer_request r

     JOIN Student s
        ON r.Student_ID = s.Student_ID

     ORDER BY
        CASE
            WHEN r.Status = "Pending" THEN 1
            WHEN r.Status = "Approved" THEN 2
            WHEN r.Status = "Rejected" THEN 3
        END,

        r.DateRequested DESC'
);

$transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Room Transfer Management</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>


<body>

<?php include 'includes/navbar.php'; ?>


<div class="page">


    <h1>
        Room Transfer Management
    </h1>


    <p class="dashboard-subtitle">
        Review and manage student room transfer requests.
    </p>


    <?php if ($message): ?>

        <p class="alert alert-success">
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <?php if ($error): ?>

        <p class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endif; ?>


    <?php if (empty($transfers)): ?>

        <p class="empty-state">
            No room transfer requests have been submitted.
        </p>


    <?php else: ?>


        <div class="request-list">


            <?php foreach ($transfers as $transfer): ?>


                <div class="request-card">


                    <div class="request-header">


                        <span>
                            Transfer
                            #<?= htmlspecialchars(
                                $transfer['Transfer_ID']
                            ) ?>
                        </span>


                        <span class="badge badge-category">

                            <?= htmlspecialchars(
                                $transfer['Status']
                            ) ?>

                        </span>


                    </div>


                    <p class="request-desc">

                        <strong>

                            <?= htmlspecialchars(
                                $transfer['FirstName'] .
                                ' ' .
                                $transfer['LastName']
                            ) ?>

                        </strong>

                    </p>


                    <div class="request-meta">


                        <span>

                            Student ID:
                            <?= htmlspecialchars(
                                $transfer['Student_ID']
                            ) ?>

                        </span>


                        <span>

                            Email:
                            <?= htmlspecialchars(
                                $transfer['Email']
                            ) ?>

                        </span>


                        <span>

                            Gender:
                            <?= htmlspecialchars(
                                $transfer['Gender']
                            ) ?>

                        </span>


                        <span>

                            Current Room:
                            <?= htmlspecialchars(
                                $transfer['Current_Room']
                            ) ?>

                        </span>


                        <span>

                            Requested Room:
                            <?= htmlspecialchars(
                                $transfer['Requested_Room']
                            ) ?>

                        </span>


                        <span>

                            Requested:
                            <?= htmlspecialchars(
                                date(
                                    'M j, Y g:i A',
                                    strtotime(
                                        $transfer['DateRequested']
                                    )
                                )
                            ) ?>

                        </span>


                    </div>


                    <p class="request-desc">

                        <strong>
                            Reason:
                        </strong>

                        <?= nl2br(
                            htmlspecialchars(
                                $transfer['Reason']
                            )
                        ) ?>

                    </p>


                    <?php if (
                        $transfer['Status'] === 'Pending'
                    ): ?>


                        <div
                            style="
                                display:flex;
                                gap:10px;
                                margin-top:15px;
                            "
                        >


                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="transfer_id"
                                    value="<?= (int) $transfer['Transfer_ID'] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="new_status"
                                    value="Approved"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Approve
                                </button>

                            </form>


                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="transfer_id"
                                    value="<?= (int) $transfer['Transfer_ID'] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="new_status"
                                    value="Rejected"
                                >

                                <button
                                    type="submit"
                                    class="btn"
                                >
                                    Reject
                                </button>

                            </form>


                        </div>


                    <?php elseif (
                        $transfer['Status'] === 'Approved'
                    ): ?>


                        <p
                            style="
                                margin-top:15px;
                                color:var(--low);
                                font-size:13px;
                                font-weight:600;
                            "
                        >

                            ✓ Transfer approved and room assignment updated.

                        </p>


                    <?php elseif (
                        $transfer['Status'] === 'Rejected'
                    ): ?>


                        <p
                            style="
                                margin-top:15px;
                                font-size:13px;
                                font-weight:600;
                            "
                        >

                            Transfer request rejected.

                        </p>


                    <?php endif; ?>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>


</body>

</html>