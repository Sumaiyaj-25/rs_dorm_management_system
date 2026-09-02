<?php
require 'includes/session_check.php';
require 'config/db.php';

// Only admins/counselors can view this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// 1. Check for Low Mood (<= 2 for 3 consecutive days)
// We will look for students who have a mood score <= 2 on 3 different dates within the last 3 days
$low_mood_query = "
    SELECT s.Student_ID, s.FirstName, s.LastName, s.Email, s.Phone_Number, s.Room_No,
           COUNT(DISTINCT m.log_date) as low_mood_days
    FROM student s
    JOIN mood_log m ON s.Student_ID = m.student_id
    WHERE m.mood_score <= 2 
      AND m.log_date >= DATE_SUB(CURDATE(), INTERVAL 2 DAY)
    GROUP BY s.Student_ID
    HAVING low_mood_days >= 3
";
$low_mood_stmt = $pdo->query($low_mood_query);
$low_mood_students = $low_mood_stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Check for Missing Meals (No meals booked in the last 3 days)
$missing_meals_query = "
    SELECT s.Student_ID, s.FirstName, s.LastName, s.Email, s.Phone_Number, s.Room_No
    FROM student s
    JOIN login l ON s.Student_ID = l.Student_ID
    WHERE s.Student_ID NOT IN (
        SELECT DISTINCT student_id 
        FROM meal 
        WHERE meal_serve_date >= DATE_SUB(CURDATE(), INTERVAL 2 DAY)
    )
    AND l.CreatedAt < DATE_SUB(CURDATE(), INTERVAL 2 DAY)
";
$missing_meals_stmt = $pdo->query($missing_meals_query);
$missing_meals_students = $missing_meals_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselor Dashboard - Well-Being Alerts</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .alert-section { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .alert-section h3 { border-bottom: 2px solid #e74c3c; padding-bottom: 10px; color: #e74c3c; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .badge { background: #e74c3c; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; }
        .no-alerts { color: #27ae60; font-weight: bold; padding: 15px 0; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="dashboard-container">
        <h2>Well-Being & Homesickness Alerts</h2>
        <p>This dashboard highlights students who may need support based on their recent mood logs and meal activity.</p>

        <div class="alert-section">
            <h3><span class="badge">Urgent</span> Persistent Low Mood</h3>
            <p>Students who have logged a mood score of "Bad" or "Terrible" for 3 consecutive days.</p>
            <?php if (count($low_mood_students) > 0): ?>
                <table>
                    <tr>
                        <th>Student Name</th>
                        <th>Room No</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($low_mood_students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($student['Room_No'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['Email']); ?></td>
                            <td><?php echo htmlspecialchars($student['Phone_Number'] ?? 'N/A'); ?></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($student['Email']); ?>" class="btn">Contact</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="no-alerts">No low mood alerts at this time.</p>
            <?php endif; ?>
        </div>

        <div class="alert-section">
            <h3><span class="badge">Warning</span> Missing Meals</h3>
            <p>Students who have not booked any meals for the past 3 days (could indicate isolation or homesickness).</p>
            <?php if (count($missing_meals_students) > 0): ?>
                <table>
                    <tr>
                        <th>Student Name</th>
                        <th>Room No</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($missing_meals_students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($student['Room_No'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['Email']); ?></td>
                            <td><?php echo htmlspecialchars($student['Phone_Number'] ?? 'N/A'); ?></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($student['Email']); ?>" class="btn">Contact</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="no-alerts">No missing meal alerts at this time.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
