<?php

require 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    $emailLower = strtolower($email);

    if (!str_ends_with($emailLower, '@bracu.ac.bd')) {

        $message = 'Staff must use a BRAC University email address (@bracu.ac.bd).';

    } else {

        $stmt = $pdo->prepare(
            "INSERT INTO Staff
            (Name, Phone_Number, Email, Role)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $name,
            $phone,
            $email,
            $role
        ]);

        $message = 'Staff Added Successfully!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Staff</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Add Staff</h1>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>
            Name
            <input
                type="text"
                name="name"
                required>
        </label>

        <br><br>

        <label>
            Phone Number
            <input
                type="text"
                name="phone"
                required>
        </label>

        <br><br>

        <label>
            Email
            <input
                type="email"
                name="email"
                required>
        </label>

        <br><br>

        <label>
            Role
            <input
                type="text"
                name="role"
                required>
        </label>

        <br><br>

        <button type="submit">
            Add Staff
        </button>

    </form>

</div>

</body>
</html>