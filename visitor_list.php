<?php
require 'includes/session_check.php';
require 'config/db.php';

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'student'
) {
    header('Location: dashboard.php');
    exit;
}

$student_id = $_SESSION['student_id'] ?? 0;

$stmt = $pdo->prepare(
    'SELECT Visitor_ID, Visitor_Name, Visitor_Phone,
            Visit_Date, QR_Code, Status, Entry_Time, Exit_Time
     FROM Visitor
     WHERE Student_ID = ?
     ORDER BY Visitor_ID DESC'
);

$stmt->execute([$student_id]);

$visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Visitors - Dorm Management</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>My Visitors</h1>

    <a href="visitor_register.php" class="btn btn-primary">
        Register New Visitor
    </a>

    <br><br>

    <?php if (empty($visitors)): ?>

        <p class="empty-state">
            You have not registered any visitors yet.
        </p>

    <?php else: ?>

        <?php foreach ($visitors as $visitor): ?>

            <div class="request-card">

                <h3>
                    <?= htmlspecialchars($visitor['Visitor_Name']) ?>
                </h3>

                <p>
                    Phone:
                    <?= htmlspecialchars($visitor['Visitor_Phone'] ?? '') ?>
                </p>

                <p>
                    Visit Date:
                    <?= htmlspecialchars($visitor['Visit_Date']) ?>
                </p>

                <p>
                    Status:
                    <?= htmlspecialchars($visitor['Status']) ?>
                </p>

                <p>
                    QR Code:
                    <strong>
                        <?= htmlspecialchars($visitor['QR_Code']) ?>
                    </strong>
                </p>
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($visitor['QR_Code']) ?>"
                    alt="Visitor QR Code"
                    width="200"
                    height="200"
                >

                <?php if ($visitor['Entry_Time'] !== null): ?>

                    <p>
                        Entry:
                        <?= htmlspecialchars($visitor['Entry_Time']) ?>
                    </p>

                <?php endif; ?>

                <?php if ($visitor['Exit_Time'] !== null): ?>

                    <p>
                        Exit:
                        <?= htmlspecialchars($visitor['Exit_Time']) ?>
                    </p>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

</body>

</html>
