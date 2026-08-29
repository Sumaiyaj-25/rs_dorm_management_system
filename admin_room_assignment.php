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



/*
|--------------------------------------------------------------------------
| Assign Room
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_id = intval($_POST['student_id'] ?? 0);
    $room_no = trim($_POST['room_no'] ?? '');

    if ($student_id <= 0) {

        $error = 'Invalid student.';

    } elseif (!$room_no) {

        $error = 'Please select a room.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Get student information
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare(
            'SELECT Student_ID, FirstName, LastName, Gender, Room_No
             FROM Student
             WHERE Student_ID = ?'
        );

        $stmt->execute([$student_id]);

        $student = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$student) {

            $error = 'Student not found.';

        } elseif ($student['Room_No'] !== null) {

            $error = 'This student already has a room assigned.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Determine allowed dorm
            |--------------------------------------------------------------------------
            */

            if ($student['Gender'] === 'Female') {

                $allowed_dorm = 'Maloncho';

            } elseif ($student['Gender'] === 'Male') {

                $allowed_dorm = 'Nikunjo';

            } else {

                $allowed_dorm = null;
            }


            if (!$allowed_dorm) {

                $error =
                    'Room cannot be assigned because the student gender is not specified correctly.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Check selected room
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare(
                    'SELECT
                        r.Room_No,
                        r.Dorm_name,
                        r.Capacity,
                        r.Status,
                        COUNT(s.Student_ID) AS Occupancy
                     FROM Room r
                     LEFT JOIN Student s
                        ON r.Room_No = s.Room_No
                     WHERE r.Room_No = ?
                     GROUP BY
                        r.Room_No,
                        r.Dorm_name,
                        r.Capacity,
                        r.Status'
                );

                $stmt->execute([$room_no]);

                $room = $stmt->fetch(PDO::FETCH_ASSOC);


                if (!$room) {

                    $error = 'Selected room does not exist.';

                } elseif ($room['Status'] !== 'Active') {

                    $error = 'Selected room is not active.';

                } elseif ($room['Dorm_name'] !== $allowed_dorm) {

                    $error =
                        'Selected room does not belong to the student\'s allowed dorm.';

                } elseif (
                    (int) $room['Occupancy'] >=
                    (int) $room['Capacity']
                ) {

                    $error = 'Selected room is already full.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Assign room
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare(
                        'UPDATE Student
                         SET Room_No = ?
                         WHERE Student_ID = ?
                         AND Room_No IS NULL'
                    );

                    $stmt->execute([
                        $room_no,
                        $student_id
                    ]);


                    if ($stmt->rowCount() > 0) {

                        $message =
                            'Room assigned successfully to ' .
                            $student['FirstName'] .
                            ' ' .
                            $student['LastName'] .
                            '.';

                    } else {

                        $error =
                            'Room assignment failed. Please try again.';
                    }
                }
            }
        }
    }
}



/*
|--------------------------------------------------------------------------
| Get students without rooms
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    'SELECT
        Student_ID,
        FirstName,
        LastName,
        Email,
        Gender,
        Department
     FROM Student
     WHERE Room_No IS NULL
     ORDER BY Student_ID'
);

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);



/*
|--------------------------------------------------------------------------
| Get available rooms with occupancy
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
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
     GROUP BY
        r.Room_No,
        r.Dorm_name,
        r.Floor,
        r.Capacity
     HAVING Occupancy < r.Capacity
     ORDER BY
        r.Dorm_name,
        r.Floor,
        r.Room_No'
);

$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="en">

<head>

```
<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Room Assignment - Dorm Management</title>

<link
    rel="stylesheet"
    href="assets/css/style.css"
>
```

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

```
<h1>
    Room Assignment
</h1>

<p class="dashboard-subtitle">
    Assign rooms to students who do not currently have a room.
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


<?php if (empty($students)): ?>

    <div class="request-card">

        <h2>
            All Students Have Rooms
        </h2>

        <p>
            There are currently no students waiting for
            room assignment.
        </p>

    </div>

<?php else: ?>


    <div class="request-list">

        <?php foreach ($students as $student): ?>

            <div class="request-card">

                <div class="request-header">

                    <span>
                        Student
                        #<?= htmlspecialchars(
                            $student['Student_ID']
                        ) ?>
                    </span>

                    <span class="badge badge-category">

                        <?= htmlspecialchars(
                            $student['Gender'] ?? 'Not specified'
                        ) ?>

                    </span>

                </div>


                <p class="request-desc">

                    <strong>

                        <?= htmlspecialchars(
                            $student['FirstName'] .
                            ' ' .
                            $student['LastName']
                        ) ?>

                    </strong>

                </p>


                <div class="request-meta">

                    <span>

                        Email:
                        <?= htmlspecialchars(
                            $student['Email']
                        ) ?>

                    </span>


                    <span>

                        Department:
                        <?= htmlspecialchars(
                            $student['Department'] ??
                            'Not specified'
                        ) ?>

                    </span>

                </div>


                <form
                    method="POST"
                    style="margin-top: 15px;"
                >

                    <input
                        type="hidden"
                        name="student_id"
                        value="<?= (int) $student['Student_ID'] ?>"
                    >


                    <label
                        for="room_<?= (int) $student['Student_ID'] ?>"
                    >
                        Assign Room
                    </label>


                    <select
                        name="room_no"
                        id="room_<?= (int) $student['Student_ID'] ?>"
                        required
                    >

                        <option value="">
                            Select a room
                        </option>


                        <?php foreach ($rooms as $room): ?>

                            <?php

                            $allowed_for_student =
                                (
                                    $student['Gender'] === 'Female' &&
                                    $room['Dorm_name'] === 'Maloncho'
                                )
                                ||
                                (
                                    $student['Gender'] === 'Male' &&
                                    $room['Dorm_name'] === 'Nikunjo'
                                );

                            ?>


                            <?php if ($allowed_for_student): ?>

                                <option
                                    value="<?= htmlspecialchars(
                                        $room['Room_No']
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $room['Room_No']
                                    ) ?>

                                    -

                                    Floor
                                    <?= htmlspecialchars(
                                        $room['Floor']
                                    ) ?>

                                    -

                                    <?= htmlspecialchars(
                                        $room['Occupancy']
                                    ) ?>

                                    /
                                    <?= htmlspecialchars(
                                        $room['Capacity']
                                    ) ?>

                                </option>

                            <?php endif; ?>

                        <?php endforeach; ?>

                    </select>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Assign Room
                    </button>

                </form>

            </div>

        <?php endforeach; ?>

    </div>


<?php endif; ?>
```

</div>

</body>

</html>

