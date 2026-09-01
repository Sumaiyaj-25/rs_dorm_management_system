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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_record'])) {
        $item_type = $_POST['item_type'];
        
        try {
            // Check if student has active clearance_id
            $stmt = $pdo->prepare("SELECT clearance_id FROM Student WHERE Student_ID = ?");
            $stmt->execute([$student_id]);
            $clearance_id = $stmt->fetchColumn() ?: null;

            $insert_stmt = $pdo->prepare("INSERT INTO laundry (item_type, owned_by, clearance_id) VALUES (?, ?, ?)");
            $insert_stmt->execute([$item_type, $student_id, $clearance_id]);
            $message = "Laundry requested successfully.";
        } catch (Exception $e) {
            $error = "Failed to request laundry: " . $e->getMessage();
        }
    }
}

// Fetch student's laundry records
$stmt = $pdo->prepare("
    SELECT * FROM laundry
    WHERE owned_by = ?
    ORDER BY item_id DESC
");
$stmt->execute([$student_id]);
$records = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Requests - Dorm Management</title>
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
        <h2>My Laundry Requests</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
            <h3>Request New Laundry</h3>
            <div class="form-group">
                <label>Item Type (e.g. 1 Bag, Dry Cleaning)</label>
                <input type="text" name="item_type" required>
            </div>
            <button type="submit" name="add_record" class="btn">Submit Request</button>
        </form>

        <h3>My Laundry Records</h3>
        <table>
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Item Type</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['item_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['item_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['laundry_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
