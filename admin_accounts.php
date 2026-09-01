<?php
require 'includes/session_check.php';
require 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_record'])) {
        $student_id = $_POST['student_id'];
        $payment_type = $_POST['payment_type'];
        
        try {
            // Check if student has active clearance_id
            $stmt = $pdo->prepare("SELECT clearance_id FROM Student WHERE Student_ID = ?");
            $stmt->execute([$student_id]);
            $clearance_id = $stmt->fetchColumn() ?: null;

            $insert_stmt = $pdo->prepare("INSERT INTO accounts (payment_type, student_id, clearance_id) VALUES (?, ?, ?)");
            $insert_stmt->execute([$payment_type, $student_id, $clearance_id]);
            $message = "Account invoice added successfully.";
        } catch (Exception $e) {
            $error = "Failed to add invoice: " . $e->getMessage();
        }
    } elseif (isset($_POST['mark_paid'])) {
        $invoice_id = $_POST['invoice_id'];
        try {
            $update_stmt = $pdo->prepare("UPDATE accounts SET payment_status = 'Paid' WHERE invoice_id = ?");
            $update_stmt->execute([$invoice_id]);
            $message = "Invoice marked as paid.";
        } catch (Exception $e) {
            $error = "Failed to update invoice: " . $e->getMessage();
        }
    }
}

// Fetch all account records
$stmt = $pdo->prepare("
    SELECT a.*, s.FirstName, s.LastName 
    FROM accounts a
    JOIN Student s ON a.student_id = s.Student_ID
    ORDER BY a.invoice_id DESC
");
$stmt->execute();
$records = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 1000px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .btn-small { padding: 5px 10px; font-size: 0.9em; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2>Manage Accounts</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
            <h3>Add New Invoice</h3>
            <div class="form-group">
                <label>Student ID</label>
                <input type="number" name="student_id" required>
            </div>
            <div class="form-group">
                <label>Payment Type (e.g. Fine, Damages)</label>
                <input type="text" name="payment_type" required>
            </div>
            <button type="submit" name="add_record" class="btn">Add Invoice</button>
        </form>

        <h3>Account Records</h3>
        <table>
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Student Name (ID)</th>
                    <th>Payment Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['invoice_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName'] . ' (' . $row['student_id'] . ')'); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                        <td>
                            <?php if ($row['payment_status'] === 'Unpaid'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="invoice_id" value="<?php echo $row['invoice_id']; ?>">
                                    <button type="submit" name="mark_paid" class="btn btn-small btn-success">Mark Paid</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
