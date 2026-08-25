<?php
require 'includes/session_check.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $token_no = $_POST['token_no'] ?? null;

    if (!$token_no) {
        header("Location: meals.php?error=Missing+token");
        exit;
    }

    // Verify it's their meal and it's not already claimed
    $stmt = $pdo->prepare("UPDATE meal SET is_released = TRUE, released_by = ?, released_status = 'Available' WHERE token_no = ? AND student_id = ? AND claim_status = FALSE");
    $stmt->execute([$student_id, $token_no, $student_id]);
    
    if ($stmt->rowCount() > 0) {
        header("Location: meals.php?success=Meal+released+successfully");
    } else {
        header("Location: meals.php?error=Could+not+release+meal");
    }
} else {
    header("Location: meals.php");
}
exit;
