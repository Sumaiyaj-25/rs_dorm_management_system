<?php
require 'includes/session_check.php';
require 'config/db.php';

// Only admins can access gate check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$search_results = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_query'])) {
    $search_query = trim($_GET['search_query']);
    
    if (!empty($search_query) && is_numeric($search_query)) {
        // Search by Student_ID
        $stmt = $pdo->prepare("
            SELECT s.Student_ID, s.FirstName, s.LastName, s.Email, s.Room_No,
                   e.clearance_status, e.cleared_at
            FROM Student s
            LEFT JOIN exit_clearance e ON s.clearance_id = e.clearance_id
            WHERE s.Student_ID = ?
        ");
        $stmt->execute([$search_query]);
        $search_results = $stmt->fetchAll();
        
        if (count($search_results) === 0) {
            $error = "No students found matching your search.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Check - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .gate-container { max-width: 800px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .search-form { display: flex; flex-direction: row; gap: 10px; margin-bottom: 30px; align-items: center; }
        .search-input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .search-btn { padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap; width: auto !important; }
        .search-btn:hover { background: #2980b9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-weight: bold; font-size: 0.9em; }
        .status-cleared { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-not-requested { background: #e2e3e5; color: #383d41; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="gate-container">
        <h2>Gate Check (Exit Clearance)</h2>
        <p>Search for a student to verify their end-of-term exit clearance status.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="GET" class="search-form">
            <input type="number" name="search_query" class="search-input" placeholder="Search by Student ID..." value="">
            <button type="submit" class="search-btn">Search</button>
        </form>

        <?php if (!empty($search_results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Clearance Status</th>
                        <th>Cleared At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($search_results as $student): ?>
                        <?php 
                            $status = $student['clearance_status'] ?? 'Not Requested';
                            $status_class = 'status-not-requested';
                            if ($status === 'Cleared') $status_class = 'status-cleared';
                            elseif ($status === 'Pending') $status_class = 'status-pending';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['Student_ID']); ?></td>
                            <td><?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($student['cleared_at'] ?? '-'); ?></td>
                            <td>
                                <?php if ($status === 'Cleared'): ?>
                                    <strong style="color: green;">Allow Exit</strong>
                                <?php else: ?>
                                    <strong style="color: red;">Block Exit</strong>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
