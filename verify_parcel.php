<?php
require 'includes/session_check.php';
require 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $parcel_id = $_POST['parcel_id'];
    $otp_code = $_POST['otp_code'];

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

        if ($parcel['OTP_Code'] == $otp_code) {

            $update = $pdo->prepare(
                "UPDATE parcel
                 SET Status = 'Collected',
                     Receive_Time = NOW()
                 WHERE P_ID = ?"
            );

            $update->execute([$parcel_id]);

            $message = 'Parcel Collected Successfully!';

        } else {

            $message = 'Incorrect OTP Code.';

        }

    } else {

        $message = 'Parcel Not Found.';

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>Verify Parcel OTP</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Verify Parcel OTP</h1>

    <?php if ($message): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>

    <form method="POST">

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
            Verify OTP
        </button>

    </form>

</div>

</body>
</html>