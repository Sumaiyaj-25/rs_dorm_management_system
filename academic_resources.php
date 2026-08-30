<?php
require 'includes/session_check.php';
require 'config/db.php';

// Allow students to view
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: dashboard.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$message = '';
$error = '';

// Handle Rating Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate_resource_id'], $_POST['rate_value'])) {
    $resource_id = (int)$_POST['rate_resource_id'];
    $rate_value = (int)$_POST['rate_value'];
    
    if ($rate_value >= 1 && $rate_value <= 5) {
        try {
            // Check if already rated
            $check_stmt = $pdo->prepare("SELECT rate_id FROM access_and_rates WHERE resource_id = ? AND student_id = ?");
            $check_stmt->execute([$resource_id, $student_id]);
            if ($check_stmt->rowCount() == 0) {
                // Insert rating
                $pdo->beginTransaction();
                
                $insert_stmt = $pdo->prepare("INSERT INTO access_and_rates (resource_id, student_id, rate_value) VALUES (?, ?, ?)");
                $insert_stmt->execute([$resource_id, $student_id, $rate_value]);
                
                // Update average rating on resource
                $avg_stmt = $pdo->prepare("SELECT AVG(rate_value) FROM access_and_rates WHERE resource_id = ?");
                $avg_stmt->execute([$resource_id]);
                $new_avg = $avg_stmt->fetchColumn();
                
                $update_res_stmt = $pdo->prepare("UPDATE academic_resources SET rating = ? WHERE resource_id = ?");
                $update_res_stmt->execute([$new_avg, $resource_id]);

                // Reward the contributor points (uploader)
                // Let's find the uploader
                $upl_stmt = $pdo->prepare("SELECT submitted_by FROM academic_resources WHERE resource_id = ?");
                $upl_stmt->execute([$resource_id]);
                $uploader_id = $upl_stmt->fetchColumn();

                if ($uploader_id) {
                    // Give 1 point to uploader for every rating received (as an example reward system)
                    $reward_stmt = $pdo->prepare("UPDATE Student SET Contributor_Points = Contributor_Points + 1 WHERE Student_ID = ?");
                    $reward_stmt->execute([$uploader_id]);
                }

                $pdo->commit();
                $message = "Rating submitted successfully!";
            } else {
                $error = "You have already rated this resource.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error submitting rating.";
        }
    } else {
        $error = "Invalid rating value.";
    }
}

// Fetch resources
$search_course = isset($_GET['course']) ? trim($_GET['course']) : '';

$query = "SELECT ar.*, s.FirstName, s.LastName 
          FROM academic_resources ar 
          JOIN Student s ON ar.submitted_by = s.Student_ID 
          WHERE ar.approval_status = 'Approved'";

$params = [];

if ($search_course !== '') {
    $query .= " AND ar.course_reference LIKE ?";
    $params[] = '%' . $search_course . '%';
}

$query .= " ORDER BY ar.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user ratings to disable rating form if already rated
$rating_stmt = $pdo->prepare("SELECT resource_id, rate_value FROM access_and_rates WHERE student_id = ?");
$rating_stmt->execute([$student_id]);
$my_ratings = [];
while ($row = $rating_stmt->fetch(PDO::FETCH_ASSOC)) {
    $my_ratings[$row['resource_id']] = $row['rate_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Resources - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 1000px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-upload { background: #3498db; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
        .btn-upload:hover { background: #2980b9; }
        .search-bar { margin-bottom: 20px; }
        .search-bar input { padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px; }
        .search-bar button { padding: 8px 15px; background: #2c3e50; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .rate-form { display: flex; align-items: center; gap: 10px; }
        .rate-form select { padding: 5px; }
        .rate-form button { padding: 5px 10px; background: #f39c12; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="header-action">
            <h2>Academic Resources & Class Notes</h2>
            <a href="resource_upload.php" class="btn-upload">Upload New Resource</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="GET" class="search-bar">
            <input type="text" name="course" placeholder="Search by course reference..." value="<?php echo htmlspecialchars($search_course); ?>">
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Course Reference</th>
                    <th>Uploaded By</th>
                    <th>Rating</th>
                    <th>File</th>
                    <th>Your Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($resources) > 0): ?>
                    <?php foreach ($resources as $res): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($res['resource_type']); ?></td>
                            <td><?php echo htmlspecialchars($res['course_reference']); ?></td>
                            <td><?php echo htmlspecialchars($res['FirstName'] . ' ' . $res['LastName']); ?></td>
                            <td><?php echo number_format($res['rating'], 1); ?> / 5.0</td>
                            <td>
                                <a href="uploads/<?php echo htmlspecialchars($res['file_path']); ?>" target="_blank">View File</a>
                            </td>
                            <td>
                                <?php if (isset($my_ratings[$res['resource_id']])): ?>
                                    You rated: <?php echo $my_ratings[$res['resource_id']]; ?>
                                <?php else: ?>
                                    <form method="POST" class="rate-form">
                                        <input type="hidden" name="rate_resource_id" value="<?php echo $res['resource_id']; ?>">
                                        <select name="rate_value" required>
                                            <option value="">Rate...</option>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Good</option>
                                            <option value="3">3 - Average</option>
                                            <option value="2">2 - Poor</option>
                                            <option value="1">1 - Terrible</option>
                                        </select>
                                        <button type="submit">Submit</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No resources found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
