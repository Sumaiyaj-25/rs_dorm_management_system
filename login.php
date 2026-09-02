<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

require 'config/db.php';

if (isset($_SESSION['student_id']) || isset($_SESSION['staff_id'])) {

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

    $isStudent = str_ends_with($emailLower, '@g.bracu.ac.bd');
    $isAdmin = str_ends_with($emailLower, '@bracu.ac.bd');

    if ($email === '' || $password === '') {

        $error = 'Please enter both email and password.';

    } elseif (!$isStudent && !$isAdmin) {

        $error = 'Please enter with your BRACU university mail.';

    } else {

        if ($isStudent) {

            $stmt = $pdo->prepare(
                'SELECT
                    s.Student_ID,
                    s.FirstName,
                    s.LastName,
                    s.Email,
                    l.PasswordHash
                 FROM Student s
                 JOIN Login l ON l.Student_ID = s.Student_ID
                 WHERE s.Email = ?
                   AND l.Staff_ID IS NULL'
            );

            $stmt->execute([$email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {

                $error = 'Please register first.';

            } elseif (!password_verify($password, $user['PasswordHash'])) {

                $error = 'Invalid email or password. Please check your credentials and try again.';

            } else {

                session_regenerate_id(true);

                $_SESSION['student_id'] = $user['Student_ID'];
                $_SESSION['name'] = $user['FirstName'] . ' ' . $user['LastName'];
                $_SESSION['first_name'] = $user['FirstName'];
                $_SESSION['role'] = 'student';
                $_SESSION['last_activity'] = time();

                $update = $pdo->prepare(
                    'UPDATE Login
                     SET LastLogin = NOW()
                     WHERE Student_ID = ?'
                );

                $update->execute([$user['Student_ID']]);

                header('Location: dashboard.php');
                exit;
            }

        } else {

            $stmt = $pdo->prepare(
                'SELECT
                    st.Staff_ID,
                    st.Name,
                    st.Email,
                    st.Role,
                    l.PasswordHash
                 FROM Staff st
                 JOIN Login l ON l.Staff_ID = st.Staff_ID
                 WHERE st.Email = ?
                   AND l.Student_ID IS NULL'
            );

            $stmt->execute([$email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {

                $error = 'Please register first.';

            } elseif (!password_verify($password, $user['PasswordHash'])) {

                $error = 'Invalid email or password. Please check your credentials and try again.';

            } else {

                session_regenerate_id(true);

                $_SESSION['staff_id'] = $user['Staff_ID'];
                $_SESSION['name'] = $user['Name'];
                $_SESSION['first_name'] = $user['Name'];
                $_SESSION['role'] = 'admin';
                $_SESSION['last_activity'] = time();

                $update = $pdo->prepare(
                    'UPDATE Login
                     SET LastLogin = NOW()
                     WHERE Staff_ID = ?'
                );

                $update->execute([$user['Staff_ID']]);

                header('Location: admin_maintenance.php');
                exit;
            }
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

            <p class="subtitle">
                Sign in to continue
            </p>

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

            <button type="submit">
                Log In
            </button>

            <p class="link-row">

                <a href="register.php">
                    Don't have an account? Register
                </a>

            </p>

        </form>

    </div>

</body>

</html>
