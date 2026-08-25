<?php
require 'includes/session_check.php';
require 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_id = trim($_POST['student_id']);
    $tracking_number = trim($_POST['tracking_number']);
    $locker_number = trim($_POST['locker_number']);
    $otp_code = trim($_POST['otp_code']);

    $status = 'Arrived';

    $stmt = $pdo->prepare(
        "INSERT INTO parcel
        (Tracking_Number, Status, Locker_Number, Arrival_Date, Receive_Time, Student_ID, OTP_Code)
        VALUES (?, ?, ?, NOW(), NULL, ?, ?)"
    );

    $stmt->execute([
        $tracking_number,
        $status,
        $locker_number,
        $student_id,
        $otp_code
    ]);

    $message = 'Parcel added successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Parcel Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Parcel Management</h1>

    <?php if ($message): ?>
        <p class="alert alert-success">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="auth-card">

        <label>
            Student ID
            <input
                type="number"
                name="student_id"
                required>
        </label>

        <br><br>

        <label>
            Tracking Number
            <input
                type="text"
                name="tracking_number"
                required>
        </label>

        <br><br>

        <label>
            Locker Number
            <input
                type="text"
                name="locker_number"
                required>
        </label>

        <br><br>

        <label>
            OTP Code
            <input
                type="text"
                name="otp_code"
                required>
        </label>

        <br><br>

        <button type="submit">
            Add Parcel
        </button>

    </form>

</div>

</body>
</html>