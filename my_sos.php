<?php

require 'includes/session_check.php';
require 'config/db.php';

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $emergency_type = $_POST['emergency_type'];

    $stmt = $pdo->prepare(
        "INSERT INTO SOS_Request
        (Emergency_Type, Student_ID, Status)
        VALUES (?, ?, 'Pending')"
    );

    $stmt->execute([
        $emergency_type,
        $student_id
    ]);

    header("Location: my_sos.php?success=1");
    exit();
}

$stmt = $pdo->prepare(
    "SELECT *
     FROM SOS_Request
     WHERE Student_ID = ?
     ORDER BY SOS_ID DESC"
);

$stmt->execute([$student_id]);

$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My SOS Requests</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>SOS Request</h1>

    <?php if (isset($_GET['success'])) : ?>
        <p style="color:green;">
            SOS Request Submitted Successfully!
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Emergency Type</label>
        <br><br>

        <select name="emergency_type" required>

            <option value="">Select Emergency</option>

            <option value="Medical Emergency">
                Medical Emergency
            </option>

            <option value="Fire Emergency">
                Fire Emergency
            </option>

            <option value="Security Emergency">
                Security Emergency
            </option>

            <option value="Other">
                Other
            </option>

        </select>

        <br><br>

        <button type="submit">
            Send SOS Request
        </button>

    </form>

    <hr>

    <h2>My SOS History</h2>

    <?php if (count($requests) > 0) : ?>

        <table border="1" cellpadding="10">

            <tr>
                <th>SOS ID</th>
                <th>Emergency Type</th>
                <th>Request Time</th>
                <th>Status</th>
            </tr>

            <?php foreach ($requests as $request) : ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($request['SOS_ID']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($request['Emergency_Type']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($request['Request_Time']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($request['Status']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    <?php else : ?>

        <p>No SOS requests found.</p>

    <?php endif; ?>

</div>

</body>
</html>