<?php

require 'includes/session_check.php';
require 'config/db.php';

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$visitor = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $visitor_id = intval($_POST['visitor_id'] ?? 0);

    if ($action === 'entry' && $visitor_id > 0) {

        $stmt = $pdo->prepare(
            'UPDATE Visitor
             SET Entry_Time = NOW(), Status = "Inside"
             WHERE Visitor_ID = ?'
        );

        $stmt->execute([$visitor_id]);

        $message = 'Visitor entry recorded successfully.';

    } elseif ($action === 'exit' && $visitor_id > 0) {

        $stmt = $pdo->prepare(
            'UPDATE Visitor
             SET Exit_Time = NOW(), Status = "Exited"
             WHERE Visitor_ID = ?'
        );

        $stmt->execute([$visitor_id]);

        $message = 'Visitor exit recorded successfully.';

    } else {

        /* QR code checking */

        $qr_code = trim($_POST['QR_Code'] ?? '');

        if ($qr_code !== '') {

            $stmt = $pdo->prepare(
                'SELECT Visitor_ID, Visitor_Name, Visitor_Phone,
                        Visit_Date, QR_Code, Status,
                        Entry_Time, Exit_Time,
                        Student_ID
                 FROM Visitor
                 WHERE QR_Code = ?'
            );

            $stmt->execute([$qr_code]);

            $visitor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$visitor) {
                $message = 'Visitor not found.';
            }
        }
    }
}


if (
    isset($_POST['action']) &&
    isset($_POST['visitor_id'])
) {

    $visitor_id = intval($_POST['visitor_id']);

    $stmt = $pdo->prepare(
        'SELECT Visitor_ID, Visitor_Name, Visitor_Phone,
                Visit_Date, QR_Code, Status,
                Entry_Time, Exit_Time,
                Student_ID
         FROM Visitor
         WHERE Visitor_ID = ?'
    );

    $stmt->execute([$visitor_id]);

    $visitor = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Visitor QR Checking</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Visitor QR Checking</h1>

    <p>
        Enter the visitor QR code to check the visitor.
    </p>


    <form method="POST">

        <input
            type="text"
            name="QR_Code"
            placeholder="Enter QR Code"
            required
        >

        <button type="submit" class="btn btn-primary">
            Check Visitor
        </button>

    </form>


    <br>


    <?php if ($message !== ''): ?>

        <p class="empty-state">
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <?php if ($visitor): ?>

        <div class="request-card">

            <h2>
                <?= htmlspecialchars($visitor['Visitor_Name']) ?>
            </h2>


            <p>
                Phone:
                <?= htmlspecialchars($visitor['Visitor_Phone'] ?? '') ?>
            </p>


            <p>
                Visit Date:
                <?= htmlspecialchars($visitor['Visit_Date']) ?>
            </p>


            <p>
                Student ID:
                <?= htmlspecialchars($visitor['Student_ID']) ?>
            </p>


            <p>
                QR Code:
                <?= htmlspecialchars($visitor['QR_Code']) ?>
            </p>


            <p>
                Status:
                <strong>
                    <?= htmlspecialchars($visitor['Status']) ?>
                </strong>
            </p>


            <?php if ($visitor['Entry_Time']): ?>

                <p>
                    Entry Time:
                    <?= htmlspecialchars($visitor['Entry_Time']) ?>
                </p>

            <?php endif; ?>


            <?php if ($visitor['Exit_Time']): ?>

                <p>
                    Exit Time:
                    <?= htmlspecialchars($visitor['Exit_Time']) ?>
                </p>

            <?php endif; ?>


            <?php if (!$visitor['Entry_Time']): ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="visitor_id"
                        value="<?= htmlspecialchars($visitor['Visitor_ID']) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="entry"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Record Entry
                    </button>

                </form>


            <?php elseif (!$visitor['Exit_Time']): ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="visitor_id"
                        value="<?= htmlspecialchars($visitor['Visitor_ID']) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="exit"
                    >

                    <button
                        type="submit"
                        class="btn btn-secondary"
                    >
                        Record Exit
                    </button>

                </form>


            <?php else: ?>

                <p>
                    Visit completed.
                </p>

            <?php endif; ?>


        </div>

    <?php endif; ?>


</div>

</body>

</html>
