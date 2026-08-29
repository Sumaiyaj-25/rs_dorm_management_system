<?php

require 'includes/session_check.php';
require 'config/db.php';

$student_id = $_SESSION['student_id'] ?? 0;

if (!$student_id) {
    header('Location: login.php');
    exit;
}


$stmt = $pdo->prepare(
    'SELECT Gender, Room_No
     FROM Student
     WHERE Student_ID = ?'
);

$stmt->execute([$student_id]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

$current_room = $student['Room_No'] ?? null;
$gender = $student['Gender'] ?? null;



$allowed_dorm = null;

if ($gender === 'Female') {
    $allowed_dorm = 'Maloncho';
} elseif ($gender === 'Male') {
    $allowed_dorm = 'Nikunjo';
}


$message = '';
$error = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $requested_room = trim($_POST['requested_room'] ?? '');
    $reason = trim($_POST['reason'] ?? '');


    if (!$current_room) {

        $error = 'You do not currently have a room assigned.';

    } elseif (!$allowed_dorm) {

        $error = 'Room transfer is only available for students with Male or Female gender specified.';

    } elseif (!$requested_room) {

        $error = 'Please select a room.';

    } elseif ($requested_room === $current_room) {

        $error = 'You are already assigned to this room.';

    } elseif (!$reason) {

        $error = 'Please provide a reason for the transfer.';

    } else {


        $stmt = $pdo->prepare(
            'SELECT Room_No, Dorm_name, Capacity
             FROM Room
             WHERE Room_No = ?
             AND Dorm_name = ?
             AND Status = "Active"'
        );

        $stmt->execute([
            $requested_room,
            $allowed_dorm
        ]);

        $room = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$room) {

            $error = 'The selected room is not available for your gender.';

        } else {


            $stmt = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM Student
                 WHERE Room_No = ?'
            );

            $stmt->execute([$requested_room]);

            $occupancy = (int) $stmt->fetchColumn();


            if ($occupancy >= (int) $room['Capacity']) {

                $error = 'The selected room is already full.';

            } else {



                $stmt = $pdo->prepare(
                    'SELECT COUNT(*)
                     FROM room_transfer_request
                     WHERE Student_ID = ?
                     AND Status = "Pending"'
                );

                $stmt->execute([$student_id]);

                $pending = (int) $stmt->fetchColumn();


                if ($pending > 0) {

                    $error = 'You already have a pending room transfer request.';

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | Create transfer request
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare(
                        'INSERT INTO room_transfer_request
                         (Student_ID, Current_Room, Requested_Room, Reason)
                         VALUES (?, ?, ?, ?)'
                    );

                    $stmt->execute([
                        $student_id,
                        $current_room,
                        $requested_room,
                        $reason
                    ]);

                    $message = 'Room transfer request submitted successfully.';
                }
            }
        }
    }
}


$rooms = [];

if ($current_room && $allowed_dorm) {

    $stmt = $pdo->prepare(
        'SELECT
            r.Room_No,
            r.Dorm_name,
            r.Floor,
            r.Capacity,
            COUNT(s.Student_ID) AS Occupancy
         FROM Room r
         LEFT JOIN Student s
            ON r.Room_No = s.Room_No
         WHERE r.Status = "Active"
         AND r.Dorm_name = ?
         AND r.Room_No <> ?
         GROUP BY
            r.Room_No,
            r.Dorm_name,
            r.Floor,
            r.Capacity
         HAVING Occupancy < r.Capacity
         ORDER BY r.Floor, r.Room_No'
    );

    $stmt->execute([
        $allowed_dorm,
        $current_room
    ]);

    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


$stmt = $pdo->prepare(
    'SELECT
        Current_Room,
        Requested_Room,
        Reason,
        Status,
        DateRequested
     FROM room_transfer_request
     WHERE Student_ID = ?
     ORDER BY DateRequested DESC'
);

$stmt->execute([$student_id]);

$transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Room Transfer - Dorm Management</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body>

<?php include 'includes/navbar.php'; ?>


<div class="page">


    <h1>
        Room Transfer
    </h1>


    <p class="dashboard-subtitle">

        Request a transfer to another available room.

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


    <?php if (!$current_room): ?>

        <div class="request-card">

            <h2>
                Room Not Assigned
            </h2>

            <p>
                You do not currently have a room assigned,
                so you cannot request a room transfer.
                Please contact the administrator for room assignment.
            </p>

        </div>


    <?php elseif (!$allowed_dorm): ?>

        <div class="request-card">

            <h2>
                Room Transfer Unavailable
            </h2>

            <p>
                Please make sure your gender is set to
                Male or Female before requesting a room transfer.
            </p>

        </div>


    <?php else: ?>


        <div class="request-card">

            <h2>
                Request Room Transfer
            </h2>


            <p>
                Current Room:
                <strong>
                    <?= htmlspecialchars($current_room) ?>
                </strong>
            </p>


            <p>
                Available Dorm:
                <strong>
                    <?= htmlspecialchars($allowed_dorm) ?>
                </strong>
            </p>


            <form method="POST">


                <label for="requested_room">
                    Requested Room
                </label>


                <select
                    name="requested_room"
                    id="requested_room"
                    required
                >

                    <option value="">
                        Select a room
                    </option>


                    <?php foreach ($rooms as $room): ?>

                        <option
                            value="<?= htmlspecialchars($room['Room_No']) ?>"
                        >

                            <?= htmlspecialchars($room['Room_No']) ?>

                            -

                            Floor
                            <?= htmlspecialchars($room['Floor']) ?>

                            -

                            <?= htmlspecialchars($room['Occupancy']) ?>
                            /
                            <?= htmlspecialchars($room['Capacity']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <?php if (empty($rooms)): ?>

                    <p class="empty-state">
                        There are currently no available rooms
                        in <?= htmlspecialchars($allowed_dorm) ?>.
                    </p>

                <?php endif; ?>


                <label for="reason">
                    Reason for Transfer
                </label>


                <textarea
                    name="reason"
                    id="reason"
                    rows="5"
                    required
                    placeholder="Please explain why you want to transfer rooms."
                ></textarea>


                <button
                    type="submit"
                    class="btn btn-primary"
                    <?= empty($rooms) ? 'disabled' : '' ?>
                >
                    Submit Transfer Request
                </button>


            </form>


        </div>


    <?php endif; ?>


    <h2>
        My Room Transfer Requests
    </h2>


    <?php if (empty($transfers)): ?>

        <p class="empty-state">
            You have not submitted any room transfer requests yet.
        </p>


    <?php else: ?>


        <div class="request-list">


            <?php foreach ($transfers as $transfer): ?>


                <div class="request-card">


                    <div class="request-header">


                        <span class="badge badge-category">

                            <?= htmlspecialchars(
                                $transfer['Current_Room']
                            ) ?>

                            &rarr;

                            <?= htmlspecialchars(
                                $transfer['Requested_Room']
                            ) ?>

                        </span>


                        <span class="badge">

                            <?= htmlspecialchars(
                                $transfer['Status']
                            ) ?>

                        </span>


                    </div>


                    <p class="request-desc">

                        <?= nl2br(
                            htmlspecialchars(
                                $transfer['Reason']
                            )
                        ) ?>

                    </p>


                    <div class="request-meta">

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


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>

</body>

</html>