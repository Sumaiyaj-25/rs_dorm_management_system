<?php
require 'includes/session_check.php';
require 'config/db.php';

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'student'
) {
    header('Location: dashboard.php');
    exit;
}

$student_id = $_SESSION['student_id'] ?? 0;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $visitor_name = trim($_POST['visitor_name'] ?? '');
    $visitor_phone = trim($_POST['visitor_phone'] ?? '');
    $visit_date = $_POST['visit_date'] ?? '';

    if ($visitor_name === '' || $visit_date === '') {

        $message = 'Please enter the visitor name and visit date.';

    } else {

        $qr_code = uniqid('VISITOR_');

        $stmt = $pdo->prepare(
            'INSERT INTO Visitor
            (Student_ID, Visitor_Name, Visitor_Phone, Visit_Date, QR_Code)
            VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $student_id,
            $visitor_name,
            $visitor_phone,
            $visit_date,
            $qr_code
        ]);

        $message = 'Visitor registered successfully. QR Code: ' . $qr_code;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register Visitor - Dorm Management</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Register Visitor</h1>

    <?php if ($message !== ''): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Visitor Name</label>

        <input
            type="text"
            name="visitor_name"
            required
        >

        <br><br>

        <label>Visitor Phone</label>

        <input
            type="text"
            name="visitor_phone"
        >

        <br><br>

        <label>Visit Date</label>

        <input
            type="date"
            name="visit_date"
            required
        >

        <br><br>

        <button type="submit" class="btn btn-primary">
            Register Visitor
        </button>

    </form>

</div>

</body>
</html>
