<?php

require 'includes/session_check.php';
require 'config/db.php';

$message = '';

if (isset($_GET['success'])) {
    $message = "Leave Request Submitted Successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $leave_date = $_POST['leave_date'];
    $return_date = $_POST['return_date'];
    $reason = $_POST['reason'];

    $stmt = $pdo->prepare(
        "INSERT INTO Leave_Request
        (Leave_Date, Return_Date, Reason, Student_ID)
        VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $leave_date,
        $return_date,
        $reason,
        $_SESSION['student_id']
    ]);

    header("Location: my_leaves.php?success=1");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT *
     FROM Leave_Request
     WHERE Student_ID = ?
     ORDER BY Request_ID DESC"
);

$stmt->execute([
    $_SESSION['student_id']
]);

$requests = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Leave Requests</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Leave Requests</h1>

    <form method="POST">

        <label>
            Leave Date
            <input
                type="date"
                name="leave_date"
                required>
        </label>

        <br><br>

        <label>
            Return Date
            <input
                type="date"
                name="return_date"
                required>
        </label>

        <br><br>

        <label>
            Reason
            <textarea
                name="reason"
                required></textarea>
        </label>

        <br><br>

        <button type="submit">
            Submit Request
        </button>

    </form>

    <br><br>

    <?php if ($message): ?>
        <p>
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <h2>My Leave History</h2>

    <table border="1" cellpadding="10">

        <tr>
            <th>Request ID</th>
            <th>Leave Date</th>
            <th>Return Date</th>
            <th>Reason</th>
            <th>Status</th>
        </tr>

        <?php foreach ($requests as $request): ?>

        <tr>

            <td>
                <?= htmlspecialchars($request['Request_ID']) ?>
            </td>

            <td>
                <?= htmlspecialchars($request['Leave_Date']) ?>
            </td>

            <td>
                <?= htmlspecialchars($request['Return_Date']) ?>
            </td>

            <td>
                <?= htmlspecialchars($request['Reason']) ?>
            </td>

            <td>
                <?= htmlspecialchars($request['Status']) ?>
            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>

</body>
</html>
