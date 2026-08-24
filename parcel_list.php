<?php
require 'includes/session_check.php';
require 'config/db.php';

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

    <?php if (empty($parcels)): ?>

        <p class="empty-state">
            No parcels found.
        </p>

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
        $p['Receive_Time'] == '0000-00-00 00:00:00' ||
        $p['Receive_Time'] == NULL
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