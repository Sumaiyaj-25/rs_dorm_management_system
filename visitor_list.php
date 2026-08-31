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
    'SELECT
        Visitor_ID,
        Visitor_Name,
        Visitor_Phone,
        Visit_Date,
        QR_Code,
        Status,
        Entry_Time,
        Exit_Time
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Visitors - Dorm Management</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>
        My Visitors
    </h1>

    <p class="dashboard-subtitle">
        View and manage the visitors you have registered.
    </p>

    <a
        href="visitor_register.php"
        class="btn btn-primary"
    >
        Register Visitor
    </a>

    <br>
    <br>


    <?php if (empty($visitors)): ?>

        <div class="request-card">

            <h2>
                No Visitors Registered
            </h2>

            <p>
                You have not registered any visitors yet.
            </p>

            <a
                href="visitor_register.php"
                class="btn btn-primary"
            >
                Register Visitor
            </a>

        </div>

    <?php else: ?>

        <div class="request-list">

            <?php foreach ($visitors as $visitor): ?>

                <div class="request-card">

                    <div class="request-header">

                        <h2>
                            <?= htmlspecialchars(
                                $visitor['Visitor_Name']
                            ) ?>
                        </h2>

                        <span class="badge badge-category">
                            <?= htmlspecialchars(
                                $visitor['Status']
                            ) ?>
                        </span>

                    </div>


                    <p>
                        <strong>Phone:</strong>
                        <?= htmlspecialchars(
                            $visitor['Visitor_Phone'] ?? ''
                        ) ?>
                    </p>


                    <p>
                        <strong>Visit Date:</strong>
                        <?= htmlspecialchars(
                            $visitor['Visit_Date']
                        ) ?>
                    </p>


                    <p>
                        <strong>QR Code:</strong>
                        <?= htmlspecialchars(
                            $visitor['QR_Code']
                        ) ?>
                    </p>


                    <p>
                        <strong>Scan QR Code:</strong>
                    </p>

                    <img
                        src="<?= htmlspecialchars(
                            'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' .
                            rawurlencode($visitor['QR_Code'])
                        ) ?>"
                        alt="Scannable QR Code"
                        width="200"
                        height="200"
                    >


                    <?php if ($visitor['Entry_Time'] !== null): ?>

                        <p>
                            <strong>Entry Time:</strong>
                            <?= htmlspecialchars(
                                $visitor['Entry_Time']
                            ) ?>
                        </p>

                    <?php endif; ?>


                    <?php if ($visitor['Exit_Time'] !== null): ?>

                        <p>
                            <strong>Exit Time:</strong>
                            <?= htmlspecialchars(
                                $visitor['Exit_Time']
                            ) ?>
                        </p>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

</body>

</html>
