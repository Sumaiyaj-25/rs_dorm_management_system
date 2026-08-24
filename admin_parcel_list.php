<?php
require 'includes/session_check.php';
require 'config/db.php';

if (isset($_GET['collect'])) {

    $pid = $_GET['collect'];

    $stmt = $pdo->prepare(
        "UPDATE parcel
         SET Status = 'Collected',
             Receive_Time = NOW()
         WHERE P_ID = ?"
    );

    $stmt->execute([$pid]);

    header('Location: admin_parcel_list.php');
    exit;
}

$stmt = $pdo->query(
    "SELECT
        P_ID,
        Student_ID,
        Tracking_Number,
        Locker_Number,
        OTP_Code,
        Status,
        Arrival_Date,
        Receive_Time
     FROM parcel
     ORDER BY Arrival_Date DESC"
);

$parcels = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>Manage Parcels</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Manage Parcels</h1>

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
                            $p['Receive_Time'] == '0000-00-00 00:00:00' ||
                            $p['Receive_Time'] == NULL
                        ) {
                            echo 'Not Collected Yet';
                        } else {
                            echo htmlspecialchars($p['Receive_Time']);
                        }
                        ?>
                    </p>

                    <?php if ($p['Status'] != 'Collected'): ?>

                        <a
                            href="admin_parcel_list.php?collect=<?= $p['P_ID'] ?>"
                            onclick="return confirm('Mark this parcel as collected?')">

                            Mark Collected

                        </a>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

</body>
</html>