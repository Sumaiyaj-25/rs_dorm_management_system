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

    // Use transaction to prevent race conditions
    $pdo->beginTransaction();

    try {
        // Find the earliest released meal of this type and date, that isn't claimed yet
        $find_stmt = $pdo->prepare("SELECT token_no FROM meal WHERE meal_type = ? AND meal_serve_date = ? AND is_released = TRUE AND claim_status = FALSE ORDER BY token_no ASC LIMIT 1 FOR UPDATE");
        $find_stmt->execute([$meal_type, $meal_serve_date]);
        
        $row = $find_stmt->fetch();

        if ($row) {
            $token_no = $row['token_no'];

            // Claim it
            $update_stmt = $pdo->prepare("UPDATE meal SET claim_status = TRUE, claimed_by = ?, released_status = 'Claimed' WHERE token_no = ?");
            $update_stmt->execute([$student_id, $token_no]);
            
            $pdo->commit();
            header("Location: meals.php?success=Meal+claimed+successfully");
        } else {
            $pdo->commit();
            header("Location: meals.php?error=No+available+meals+to+claim");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: meals.php?error=Transaction+failed");
    }
} else {
    header("Location: meals.php");
}
exit;
