<?php
require 'includes/session_check.php';
require 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: dashboard.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$message = '';
$error = '';

// Check current clearance status
$stmt = $pdo->prepare("SELECT clearance_id FROM Student WHERE Student_ID = ?");
$stmt->execute([$student_id]);
$clearance_id = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_clearance'])) {
    if (!$clearance_id) {
        try {
            $pdo->beginTransaction();
            // Create clearance record
            $insert_stmt = $pdo->prepare("INSERT INTO exit_clearance (clearance_status) VALUES ('Pending')");
            $insert_stmt->execute();
            $new_clearance_id = $pdo->lastInsertId();

            // Update student
            $update_student = $pdo->prepare("UPDATE Student SET clearance_id = ? WHERE Student_ID = ?");
            $update_student->execute([$new_clearance_id, $student_id]);

            // Link pending items to this clearance
            $pdo->prepare("UPDATE laundry SET clearance_id = ? WHERE owned_by = ? AND payment_status != 'Paid'")->execute([$new_clearance_id, $student_id]);
            $pdo->prepare("UPDATE library SET clearance_id = ? WHERE booked_by = ? AND booked_status != 'Returned'")->execute([$new_clearance_id, $student_id]);
            $pdo->prepare("UPDATE accounts SET clearance_id = ? WHERE student_id = ? AND payment_status != 'Paid'")->execute([$new_clearance_id, $student_id]);

            $pdo->commit();
            $clearance_id = $new_clearance_id;
            $message = "Clearance requested successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to request clearance: " . $e->getMessage();
        }
    }
}

$laundry_pending = 0;
$library_pending = 0;
$accounts_pending = 0;
$overall_status = 'Not Requested';

if ($clearance_id) {
    // Check pending counts
    $laundry_stmt = $pdo->prepare("SELECT COUNT(*) FROM laundry WHERE clearance_id = ? AND payment_status != 'Paid'");
    $laundry_stmt->execute([$clearance_id]);
    $laundry_pending = $laundry_stmt->fetchColumn();

    $library_stmt = $pdo->prepare("SELECT COUNT(*) FROM library WHERE clearance_id = ? AND booked_status != 'Returned'");
    $library_stmt->execute([$clearance_id]);
    $library_pending = $library_stmt->fetchColumn();

    $accounts_stmt = $pdo->prepare("SELECT COUNT(*) FROM accounts WHERE clearance_id = ? AND payment_status != 'Paid'");
    $accounts_stmt->execute([$clearance_id]);
    $accounts_pending = $accounts_stmt->fetchColumn();

    $total_pending = $laundry_pending + $library_pending + $accounts_pending;

    $status_stmt = $pdo->prepare("SELECT clearance_status FROM exit_clearance WHERE clearance_id = ?");
    $status_stmt->execute([$clearance_id]);
    $current_db_status = $status_stmt->fetchColumn();

    if ($total_pending == 0 && $current_db_status !== 'Cleared') {
        // Update to cleared
        $pdo->prepare("UPDATE exit_clearance SET clearance_status = 'Cleared', cleared_at = NOW() WHERE clearance_id = ?")->execute([$clearance_id]);
        $overall_status = 'Cleared';
    } elseif ($total_pending == 0) {
        $overall_status = 'Cleared';
    } else {
        $overall_status = 'Pending';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exit Clearance - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .clearance-container { max-width: 800px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .department-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-weight: bold; font-size: 0.9em; }
        .status-cleared { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .overall-status { font-size: 1.2em; text-align: center; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .btn { padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #2980b9; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="clearance-container">
        <h2>End-of-Term Exit Clearance</h2>
        <p>You must obtain clearance from all departments before leaving the dormitory.</p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="overall-status">
            <strong>Overall Status:</strong> 
            <span class="status-badge <?php echo $overall_status === 'Cleared' ? 'status-cleared' : 'status-pending'; ?>">
                <?php echo htmlspecialchars($overall_status); ?>
            </span>
        </div>

        <?php if (!$clearance_id): ?>
            <div style="text-align: center;">
                <form method="POST">
                    <button type="submit" name="request_clearance" class="btn">Request Clearance</button>
                </form>
            </div>
        <?php else: ?>
            <div class="department-card">
                <div>
                    <h3>Laundry Department</h3>
                    <p>Pending Unpaid Items: <?php echo $laundry_pending; ?></p>
                </div>
                <span class="status-badge <?php echo $laundry_pending == 0 ? 'status-cleared' : 'status-pending'; ?>">
                    <?php echo $laundry_pending == 0 ? 'Cleared' : 'Pending'; ?>
                </span>
            </div>

            <div class="department-card">
                <div>
                    <h3>Library Department</h3>
                    <p>Unreturned Books: <?php echo $library_pending; ?></p>
                </div>
                <span class="status-badge <?php echo $library_pending == 0 ? 'status-cleared' : 'status-pending'; ?>">
                    <?php echo $library_pending == 0 ? 'Cleared' : 'Pending'; ?>
                </span>
            </div>

            <div class="department-card">
                <div>
                    <h3>Accounts Department</h3>
                    <p>Unpaid Invoices: <?php echo $accounts_pending; ?></p>
                </div>
                <span class="status-badge <?php echo $accounts_pending == 0 ? 'status-cleared' : 'status-pending'; ?>">
                    <?php echo $accounts_pending == 0 ? 'Cleared' : 'Pending'; ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
