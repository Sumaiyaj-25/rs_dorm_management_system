<?php

require 'config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare(
    "SELECT * FROM Staff
     WHERE Staff_ID = ?"
);

$stmt->execute([$id]);

$staff = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $update = $pdo->prepare(
        "UPDATE Staff
         SET Name = ?,
             Phone_Number = ?,
             Email = ?,
             Role = ?
         WHERE Staff_ID = ?"
    );

    $update->execute([
        $name,
        $phone,
        $email,
        $role,
        $id
    ]);

    header('Location: staff_list.php');
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Staff</title>
</head>
<body>

<h1>Edit Staff</h1>

<form method="POST">

    <label>
        Name
        <input
            type="text"
            name="name"
            value="<?= $staff['Name'] ?>"
            required>
    </label>

    <br><br>

    <label>
        Phone Number
        <input
            type="text"
            name="phone"
            value="<?= $staff['Phone_Number'] ?>"
            required>
    </label>

    <br><br>

    <label>
        Email
        <input
            type="email"
            name="email"
            value="<?= $staff['Email'] ?>"
            required>
    </label>

    <br><br>

    <label>
        Role
        <input
            type="text"
            name="role"
            value="<?= $staff['Role'] ?>"
            required>
    </label>

    <br><br>

    <button type="submit">
        Update Staff
    </button>

</form>

</body>
</html>