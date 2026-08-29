<?php
require 'includes/session_check.php';
require 'config/db.php';

$stmt = $pdo->prepare(
    'SELECT RequestID, DateSubmitted, Description, Photo, Category, Priority, Status, Room_No
     FROM maintenance_request
     WHERE Student_ID = ?
     ORDER BY FIELD(Priority, "High", "Medium", "Low"), DateSubmitted DESC'
);
$stmt->execute([$_SESSION['student_id']]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Maintenance Requests</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page">
    <h1>My Maintenance Requests</h1>

    <?php if (empty($requests)): ?>
        <p class="empty-state">No requests yet. <a href="maintenance_submit.php">Submit your first one</a>.</p>
    <?php else: ?>
        <div class="request-list">
            <?php foreach ($requests as $r): ?>
                <div class="request-card priority-<?= strtolower($r['Priority']) ?>">
                    <div class="request-header">
                        <span class="badge badge-priority-<?= strtolower($r['Priority']) ?>"><?= htmlspecialchars($r['Priority']) ?></span>
                        <span class="badge badge-category"><?= htmlspecialchars($r['Category']) ?></span>
                        <span class="badge badge-status-<?= strtolower(str_replace(' ', '-', $r['Status'])) ?>"><?= htmlspecialchars($r['Status']) ?></span>
                    </div>
                    <p class="request-desc"><?= nl2br(htmlspecialchars($r['Description'])) ?></p>
                    <div class="request-meta">
                        <span>Room: <?= htmlspecialchars($r['Room_No']) ?></span>
                        <span>Submitted: <?= htmlspecialchars(date('M j, Y g:i A', strtotime($r['DateSubmitted']))) ?></span>
                    </div>
                    <?php if ($r['Photo']): ?>
                        <img class="request-photo" src="<?= htmlspecialchars($r['Photo']) ?>" alt="Issue photo">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
     <div style="text-align: center; margin-top: 30px;">
        <a href="maintenance_submit.php" class="btn btn-primary">
            Submit New Maintenance Request
        </a>
    </div>
</div>
</body>
</html>
