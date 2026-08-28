<?php

require 'config/db.php';

$stmt = $pdo->query(
    "SELECT * FROM Staff ORDER BY Staff_ID DESC"
);

$staffs = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff List</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1>Staff List</h1>

<table border="1">

    <tr>
        <th>Staff ID</th>
        <th>Name</th>
        <th>Phone Number</th>
        <th>Email</th>
        <th>Role</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>

    <?php foreach ($staffs as $staff): ?>

    <tr>

        <td><?= $staff['Staff_ID'] ?></td>

        <td><?= $staff['Name'] ?></td>

        <td><?= $staff['Phone_Number'] ?></td>

        <td><?= $staff['Email'] ?></td>

        <td><?= $staff['Role'] ?></td>

        <td>
            <a href="staff_edit.php?id=<?= $staff['Staff_ID'] ?>">
                Edit
            </a>
        </td>

        <td>
            <a href="staff_delete.php?id=<?= $staff['Staff_ID'] ?>">
                Delete
            </a>
        </td>

    </tr>

    <?php endforeach; ?>

</table>

</body>
</html>