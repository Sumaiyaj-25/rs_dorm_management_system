<?php
require 'includes/session_check.php';
require 'config/db.php';

// Only staff (admin) can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}


$message = '';
$error = '';

// Handle Approval / Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['resource_id'])) {
    $resource_id = (int)$_POST['resource_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 'Approved' : 'Rejected';
        
        try {
            $staff_id = $_SESSION['staff_id'];
            $update_stmt = $pdo->prepare("UPDATE academic_resources SET approval_status = ?, moderator_id = ? WHERE resource_id = ?");
            if ($update_stmt->execute([$status, $staff_id, $resource_id])) {
                $message = "Resource has been " . strtolower($status) . ".";
            } else {
                $error = "Failed to update resource status.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}


// Fetch pending resources
$query = "SELECT ar.*, s.FirstName, s.LastName 
          FROM academic_resources ar 
          JOIN Student s ON ar.submitted_by = s.Student_ID 
          WHERE ar.approval_status = 'Pending'
          ORDER BY ar.created_at ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$pending_resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 1000px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .action-form { display: inline-block; margin-right: 5px; }
        .btn-approve { background: #2ecc71; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-reject { background: #e74c3c; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2>Moderator Dashboard - Pending Resources</h2>
        <p>Review uploaded academic resources before they are visible to students.</p>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Course Reference</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th>File</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pending_resources) > 0): ?>
                    <?php foreach ($pending_resources as $res): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($res['resource_type']); ?></td>
                            <td><?php echo htmlspecialchars($res['course_reference']); ?></td>
                            <td><?php echo htmlspecialchars($res['FirstName'] . ' ' . $res['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($res['created_at']); ?></td>
                            <td>
                                <a href="uploads/<?php echo htmlspecialchars($res['file_path']); ?>" target="_blank">View File</a>
                            </td>
                            <td>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="resource_id" value="<?php echo $res['resource_id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-approve">Approve</button>
                                </form>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="resource_id" value="<?php echo $res['resource_id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No pending resources to review.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
