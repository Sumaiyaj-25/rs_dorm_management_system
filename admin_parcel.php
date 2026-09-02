<?php
require 'includes/session_check.php';
require 'config/db.php';

$message = '';

if (isset($_GET['success'])) {
    $message = 'Parcel added successfully!';
}

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

    header('Location: admin_parcel.php?success=1');
      exit; 
}



$search = '';

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search != '') {

    $stmt = $pdo->prepare(
        "SELECT *
         FROM parcel
         WHERE Student_ID LIKE ?
            OR Tracking_Number LIKE ?
         ORDER BY Arrival_Date DESC"
    );

    $stmt->execute([
        "%$search%",
        "%$search%"
    ]);

} else {

    $stmt = $pdo->query(
        "SELECT *
         FROM parcel
         ORDER BY Arrival_Date DESC"
    );
}

$parcels = $stmt->fetchAll();
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
<p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<h2>Add Parcel</h2>

<form method="POST" class="auth-card">

<label>
Student ID
<input type="number" name="student_id" required>
</label>

<br><br>

<label>
Tracking Number
<input type="text" name="tracking_number" required>
</label>

<br><br>

<label>
Locker Number
<input type="text" name="locker_number" required>
</label>

<br><br>

<label>
OTP Code
<input type="text" name="otp_code" required>
</label>

<br><br>

<button type="submit">
Add Parcel
</button>

</form>

<hr>

<h2>Search Parcel</h2>

<form method="GET">

<input
type="text"
name="search"
placeholder="Student ID or Tracking Number"
value="<?= htmlspecialchars($search) ?>">

<button type="submit">
Search
</button>

</form>

<hr>

<h2>All Parcels</h2>

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
<strong>Student ID:</strong>
<?= htmlspecialchars($p['Student_ID']) ?>
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