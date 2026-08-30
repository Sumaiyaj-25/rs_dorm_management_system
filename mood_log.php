<?php
require 'includes/session_check.php';
require 'config/db.php';

// Only students can log mood
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: dashboard.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$today = date('Y-m-d');
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mood_score'])) {
    $mood_score = (int)$_POST['mood_score'];
    
    if ($mood_score >= 1 && $mood_score <= 5) {
        // Check if already logged today
        $check_stmt = $pdo->prepare("SELECT log_id FROM mood_log WHERE student_id = ? AND log_date = ?");
        $check_stmt->execute([$student_id, $today]);
        
        if ($check_stmt->rowCount() > 0) {
            // Update today's mood
            $update_stmt = $pdo->prepare("UPDATE mood_log SET mood_score = ? WHERE student_id = ? AND log_date = ?");
            if ($update_stmt->execute([$mood_score, $student_id, $today])) {
                $message = "Mood updated successfully for today!";
            } else {
                $error = "Failed to update mood.";
            }
        } else {
            // Insert new mood log
            $insert_stmt = $pdo->prepare("INSERT INTO mood_log (student_id, log_date, mood_score) VALUES (?, ?, ?)");
            if ($insert_stmt->execute([$student_id, $today, $mood_score])) {
                $message = "Mood logged successfully!";
            } else {
                $error = "Failed to log mood.";
            }
        }
    } else {
        $error = "Invalid mood score selected.";
    }
}

// Fetch current mood for today if exists
$current_mood_stmt = $pdo->prepare("SELECT mood_score FROM mood_log WHERE student_id = ? AND log_date = ?");
$current_mood_stmt->execute([$student_id, $today]);
$current_mood = $current_mood_stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Mood - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .mood-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .mood-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }
        .mood-option {
            background: none;
            border: 2px solid transparent;
            font-size: 3rem;
            cursor: pointer;
            border-radius: 50%;
            padding: 10px;
            transition: all 0.2s ease;
        }
        .mood-option:hover {
            transform: scale(1.1);
            background: #f0f0f0;
        }
        .mood-option.selected {
            border-color: #3498db;
            background: #e1f5fe;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="mood-container">
        <h2>How are you feeling today?</h2>
        <p>Your well-being is important to us. Select an emoji to track your mood.</p>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="mood_log.php">
            <div class="mood-options">
                <button type="submit" name="mood_score" value="1" class="mood-option <?php echo ($current_mood == 1) ? 'selected' : ''; ?>" title="Terrible">😫</button>
                <button type="submit" name="mood_score" value="2" class="mood-option <?php echo ($current_mood == 2) ? 'selected' : ''; ?>" title="Bad">😟</button>
                <button type="submit" name="mood_score" value="3" class="mood-option <?php echo ($current_mood == 3) ? 'selected' : ''; ?>" title="Okay">😐</button>
                <button type="submit" name="mood_score" value="4" class="mood-option <?php echo ($current_mood == 4) ? 'selected' : ''; ?>" title="Good">🙂</button>
                <button type="submit" name="mood_score" value="5" class="mood-option <?php echo ($current_mood == 5) ? 'selected' : ''; ?>" title="Great">😁</button>
            </div>
            <p>Score: 1 (Terrible) to 5 (Great)</p>
        </form>
    </div>
</body>
</html>
