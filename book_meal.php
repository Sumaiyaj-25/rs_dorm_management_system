<?php
require 'includes/session_check.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $meal_type = $_POST['meal_type'] ?? '';
    $meal_serve_date = $_POST['meal_serve_date'] ?? '';

    if (empty($meal_type) || empty($meal_serve_date)) {
        header("Location: meals.php?error=Missing+fields");
        exit;
    }

    // Check if already booked
    $check_stmt = $pdo->prepare("SELECT token_no FROM meal WHERE student_id = ? AND meal_type = ? AND meal_serve_date = ?");
    $check_stmt->execute([$student_id, $meal_type, $meal_serve_date]);
    if ($check_stmt->rowCount() > 0) {
        header("Location: meals.php?error=Meal+already+booked");
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO meal (meal_type, student_id, meal_serve_date) VALUES (?, ?, ?)");
    if ($stmt->execute([$meal_type, $student_id, $meal_serve_date])) {
        header("Location: meals.php?success=Meal+booked+successfully");
    } else {
        header("Location: meals.php?error=Failed+to+book+meal");
    }
} else {
    header("Location: meals.php");
}
exit;
