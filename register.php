<?php
require 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $gender    = trim($_POST['gender'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password  = $_POST['password'] ?? '';

    $emailLower = strtolower($email);
    $isValidDomain = str_ends_with($emailLower, '@g.bracu.ac.bd') || str_ends_with($emailLower, '@bracu.ac.bd');

    if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!$isValidDomain) {
        $error = 'Please use a valid BRAC University email address (@g.bracu.ac.bd or @bracu.ac.bd).';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $check = $pdo->prepare('SELECT Student_ID FROM student WHERE Email = ?');
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO student (FirstName, LastName, Email, Phone_Number, Gender, Department)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$firstName, $lastName, $email, $phoneNumber, $gender, $department]);
                $studentId = $pdo->lastInsertId();

                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare(
                    'INSERT INTO login (Student_ID, PasswordHash) VALUES (?, ?)'
                );
                $stmt->execute([$studentId, $hash]);

                $pdo->commit();
                $success = 'Account created! You can now log in.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register — Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <form class="auth-card" method="POST" novalidate>
            <h1>Create Account</h1>

            <?php if ($error): ?>
                <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <p class="alert alert-success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <label>First Name
                <input type="text" name="first_name" required>
            </label>
            
            <label>Last Name
                <input type="text" name="last_name" required>
            </label>
            
            <label>Email
                <input type="email" name="email" required>
            </label>
            
            <label>Phone Number
                <input type="tel" name="phone_number" placeholder="e.g. 01*********">
            </label>
            
            <label>Gender
                <select name="gender">
                    <option value="">Prefer not to say</option>
                    <option value="Female">Female</option>
                    <option value="Male">Male</option>
                    <option value="Other">Other</option>
                </select>
            </label>
            
            <label>Department
                <input type="text" name="department" placeholder="e.g. CSE">
            </label>
            
            <label>Password
                <input type="password" name="password" required minlength="6">
            </label>

            <button type="submit">Register</button>
            <p class="link-row"><a href="login.php">Already have an account? Log in</a></p>
        </form>
    </div>
</body>
</html>
