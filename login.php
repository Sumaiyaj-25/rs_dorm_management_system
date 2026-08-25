<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

require 'config/db.php';

if (isset($_SESSION['student_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: admin_maintenance.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $emailLower = strtolower($email);

    if ($email === '' || $password === '') {

        $error = 'Please enter both email and password.';

    } elseif (
        !str_ends_with($emailLower, '@g.bracu.ac.bd') &&
        !str_ends_with($emailLower, '@bracu.ac.bd')
    ) {

        $error = 'Please enter with your BRACU university mail.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT
                s.Student_ID,
                s.FirstName,
                s.LastName,
                s.Email,
                l.PasswordHash
             FROM student s
             JOIN login l ON l.Student_ID = s.Student_ID
             WHERE s.Email = ?'
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {

            $error = 'Please register first.';

        } elseif (!password_verify($password, $user['PasswordHash'])) {

            $error = 'Invalid email or password. Please check your credentials and try again.';

        } else {

            if (str_ends_with($emailLower, '@g.bracu.ac.bd')) {
                $role = 'student';
            } else {
                $role = 'admin';
            }

            session_regenerate_id(true);

            $_SESSION['student_id'] = $user['Student_ID'];
            $_SESSION['name'] = $user['FirstName'] . ' ' . $user['LastName'];
            $_SESSION['first_name'] = $user['FirstName'];
            $_SESSION['role'] = $role;
            $_SESSION['last_activity'] = time();

            $update = $pdo->prepare(
                'UPDATE login SET LastLogin = NOW() WHERE Student_ID = ?'
            );

            $update->execute([$user['Student_ID']]);

            if ($role === 'admin') {
                header('Location: admin_maintenance.php');
            } else {
                header('Location: dashboard.php');
            }

            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="auth-wrapper">

        <form class="auth-card" method="POST" novalidate>

            <h1>Dorm Management</h1>

            <p class="subtitle">Sign in to continue</p>

            <?php if ($error): ?>

                <p class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </p>

            <?php endif; ?>

            <label>
                Email
                <input
                    type="email"
                    name="email"
                    required
                    autofocus
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </label>

            <label>
                Password
                <input
                    type="password"
                    name="password"
                    required
                >
            </label>

            <button type="submit">Log In</button>

            <p class="link-row">
                <a href="register.php">Don't have an account? Register</a>
            </p>

        </form>

    </div>

</body>
</html>
