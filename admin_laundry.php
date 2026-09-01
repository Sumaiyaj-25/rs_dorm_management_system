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
    if (isset($_POST['update_status'])) {
        $item_id = $_POST['item_id'];
        $new_status = $_POST['laundry_status'];
        
        try {
            $update_stmt = $pdo->prepare("UPDATE laundry SET laundry_status = ? WHERE item_id = ?");
            $update_stmt->execute([$new_status, $item_id]);
            $message = "Laundry status updated to " . htmlspecialchars($new_status) . ".";
        } catch (Exception $e) {
            $error = "Failed to update record: " . $e->getMessage();
        }
    } elseif (isset($_POST['mark_paid'])) {
        $item_id = $_POST['item_id'];
        try {
            $update_stmt = $pdo->prepare("UPDATE laundry SET payment_status = 'Paid' WHERE item_id = ?");
            $update_stmt->execute([$item_id]);
            $message = "Laundry payment status updated to Paid.";
        } catch (Exception $e) {
            $error = "Failed to update record: " . $e->getMessage();
        }
    }
}

// Fetch all laundry records
$stmt = $pdo->prepare("
    SELECT l.*, s.FirstName, s.LastName 
    FROM laundry l
    JOIN Student s ON l.owned_by = s.Student_ID
    ORDER BY l.item_id DESC
");
$stmt->execute();
$records = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Laundry - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 1000px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn { padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .btn-small { padding: 5px 10px; font-size: 0.9em; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: middle; }
        th { background-color: #f8f9fa; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        select { padding: 5px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2>Manage Laundry Requests</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Student Name (ID)</th>
                    <th>Item Type</th>
                    <th>Payment Status</th>
                    <th>Current Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['item_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName'] . ' (' . $row['owned_by'] . ')'); ?></td>
                        <td><?php echo htmlspecialchars($row['item_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['laundry_status']); ?></td>
                        <td>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <form method="POST" style="display:inline; display:flex; gap: 10px; align-items: center;">
                                    <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                                    <select name="laundry_status">
                                        <option value="Pending" <?php echo $row['laundry_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Washing" <?php echo $row['laundry_status'] === 'Washing' ? 'selected' : ''; ?>>Washing</option>
                                        <option value="Ready" <?php echo $row['laundry_status'] === 'Ready' ? 'selected' : ''; ?>>Ready</option>
                                        <option value="Returned" <?php echo $row['laundry_status'] === 'Returned' ? 'selected' : ''; ?>>Returned</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-small">Update</button>
                                </form>
                                <?php if ($row['payment_status'] === 'Unpaid'): ?>
                                    <form method="POST" style="display:inline; margin: 0;">
                                        <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                                        <button type="submit" name="mark_paid" class="btn btn-small btn-success">Mark Paid</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
