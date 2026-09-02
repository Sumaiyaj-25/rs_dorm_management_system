<?php

require 'includes/session_check.php';
require 'config/db.php';

$message = '';

/* ---------------- ADD STAFF ---------------- */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_staff'])
) {

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    $emailLower = strtolower($email);

    if (!str_ends_with($emailLower, '@bracu.ac.bd')) {

        $message =
        'Staff must use a BRAC University email address (@bracu.ac.bd).';

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

        $message = 'Staff added successfully!';
    }
}

/* ---------------- DELETE STAFF ---------------- */

if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $stmt = $pdo->prepare(
        "DELETE FROM Staff
         WHERE Staff_ID = ?"
    );

    $stmt->execute([$id]);

    header('Location: staff_management.php');
    exit;
}

/* ---------------- UPDATE STAFF ---------------- */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_staff'])
) {

    $id = $_POST['staff_id'];

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    $stmt = $pdo->prepare(
        "UPDATE Staff
         SET Name = ?,
             Phone_Number = ?,
             Email = ?,
             Role = ?
         WHERE Staff_ID = ?"
    );

    $stmt->execute([
        $name,
        $phone,
        $email,
        $role,
        $id
    ]);

    header('Location: staff_management.php');
    exit;
}

/* ---------------- SEARCH STAFF ---------------- */

$search = '';

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search != '') {

    $stmt = $pdo->prepare(
        "SELECT *
         FROM Staff
         WHERE Name LIKE ?
            OR Email LIKE ?
            OR Role LIKE ?
         ORDER BY Staff_ID DESC"
    );

    $stmt->execute([
        "%$search%",
        "%$search%",
        "%$search%"
    ]);

} else {

    $stmt = $pdo->query(
        "SELECT *
         FROM Staff
         ORDER BY Staff_ID DESC"
    );
}

$staffs = $stmt->fetchAll();

/* ---------------- EDIT STAFF ---------------- */

$editStaff = null;

if (isset($_GET['edit'])) {

    $stmt = $pdo->prepare(
        "SELECT *
         FROM Staff
         WHERE Staff_ID = ?"
    );

    $stmt->execute([$_GET['edit']]);

    $editStaff = $stmt->fetch();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>Staff Management</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

<h1>Staff Management</h1>

<?php if ($message): ?>
<p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<h2>
<?= $editStaff ? 'Edit Staff' : 'Add Staff' ?>
</h2>

<form method="POST" class="auth-card">

<?php if ($editStaff): ?>
<input
type="hidden"
name="staff_id"
value="<?= $editStaff['Staff_ID'] ?>">
<?php endif; ?>

<label>
Name
<input
type="text"
name="name"
value="<?= $editStaff ? htmlspecialchars($editStaff['Name']) : '' ?>"
required>
</label>

<br><br>

<label>
Phone Number
<input
type="text"
name="phone"
value="<?= $editStaff ? htmlspecialchars($editStaff['Phone_Number']) : '' ?>"
required>
</label>

<br><br>

<label>
Email
<input
type="email"
name="email"
value="<?= $editStaff ? htmlspecialchars($editStaff['Email']) : '' ?>"
required>
</label>

<br><br>

<label>
Role
<input
type="text"
name="role"
value="<?= $editStaff ? htmlspecialchars($editStaff['Role']) : '' ?>"
required>
</label>

<br><br>

<?php if ($editStaff): ?>

<button
type="submit"
name="update_staff">
Update Staff
</button>

<a href="staff_management.php">
Cancel
</a>

<?php else: ?>

<button
type="submit"
name="add_staff">
Add Staff
</button>

<?php endif; ?>

</form>

<hr>

<h2>Search Staff</h2>

<form method="GET">

<input
type="text"
name="search"
placeholder="Search Name, Email or Role"
value="<?= htmlspecialchars($search) ?>">

<button type="submit">
Search
</button>

</form>

<hr>

<h2>All Staff</h2>

<?php if (empty($staffs)): ?>

<p>No staff found.</p>

<?php else: ?>

<table border="1" cellpadding="8">

<tr>
<th>ID</th>
<th>Name</th>
<th>Phone</th>
<th>Email</th>
<th>Role</th>
<th>Edit</th>
<th>Delete</th>
</tr>

<?php foreach ($staffs as $staff): ?>

<tr>

<td><?= htmlspecialchars($staff['Staff_ID']) ?></td>

<td><?= htmlspecialchars($staff['Name']) ?></td>

<td><?= htmlspecialchars($staff['Phone_Number']) ?></td>

<td><?= htmlspecialchars($staff['Email']) ?></td>

<td><?= htmlspecialchars($staff['Role']) ?></td>

<td>
<a href="staff_management.php?edit=<?= $staff['Staff_ID'] ?>">
Edit
</a>
</td>

<td>
<a
href="staff_management.php?delete=<?= $staff['Staff_ID'] ?>"
onclick="return confirm('Delete this staff member?')">
Delete
</a>
</td>

</tr>

<?php endforeach; ?>

</table>

<?php endif; ?>

</div>

</body>
</html>