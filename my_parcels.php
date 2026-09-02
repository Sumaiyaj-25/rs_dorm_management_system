<?php
require 'includes/session_check.php';
require 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $parcel_id = $_POST['parcel_id'];
    $otp_code = trim($_POST['otp_code']);

    $stmt = $pdo->prepare(
        "SELECT *
         FROM parcel
         WHERE P_ID = ?
         AND Student_ID = ?"
    );

    $stmt->execute([
        $parcel_id,
        $_SESSION['student_id']
    ]);

    $parcel = $stmt->fetch();

    if ($parcel) {

        if (
            $parcel['OTP_Code'] == $otp_code &&
            $parcel['Status'] != 'Collected'
        ) {

            $update = $pdo->prepare(
                "UPDATE parcel
                 SET Status = 'Collected',
                     Receive_Time = NOW()
                 WHERE P_ID = ?"
            );

            $update->execute([$parcel_id]);

            $message = 'Parcel collected successfully!';

        } else {

            $message = 'Invalid OTP or parcel already collected.';
        }

    } else {

        $message = 'Parcel not found.';
    }
}

$stmt = $pdo->prepare(
    "SELECT
        P_ID,
        Tracking_Number,
        Status,
        Locker_Number,
        Arrival_Date,
        Receive_Time,
        OTP_Code
     FROM parcel
     WHERE Student_ID = ?
     ORDER BY Arrival_Date DESC"
);

$stmt->execute([$_SESSION['student_id']]);

$parcels = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>My Parcels</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

<h1>My Parcels</h1>

<?php if ($message): ?>
<p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<h2>Verify OTP</h2>

<form method="POST" class="auth-card">

<label>
Parcel ID
<input
type="number"
name="parcel_id"
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
Collect Parcel
</button>

</form>

<hr>

<h2>Parcel History</h2>

<?php if (empty($parcels)): ?>

<p>No parcels found.</p>

<?php else: ?>

<div class="request-list">

<?php foreach ($parcels as $p): ?>

<div class="request-card">

<p>
<strong>Parcel ID:</strong>
<?= htmlspecialchars($p['P_ID']) ?>
</p>

<p>
<strong>Tracking Number:</strong>
<?= htmlspecialchars($p['Tracking_Number']) ?>
</p>

<p>
<strong>Locker Number:</strong>
<?= htmlspecialchars($p['Locker_Number']) ?>
</p>

<p>
<strong>OTP Code:</strong>
<?= htmlspecialchars($p['OTP_Code']) ?>
</p>

<p>
<strong>Status:</strong>
<?= htmlspecialchars($p['Status']) ?>
</p>

<p>
<strong>Arrival Date:</strong>
<?= htmlspecialchars($p['Arrival_Date']) ?>
</p>

<p>
<strong>Receive Time:</strong>

<?php
if (
    $p['Receive_Time'] == NULL ||
    $p['Receive_Time'] == '0000-00-00 00:00:00'
) {
    echo 'Not Collected Yet';
} else {
    echo htmlspecialchars($p['Receive_Time']);
}
?>
</p>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>

</body>
</html>