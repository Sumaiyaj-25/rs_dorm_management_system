<?php

require 'includes/session_check.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $sos_id = $_POST['sos_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare(
        "UPDATE SOS_Request
         SET Status = ?
         WHERE SOS_ID = ?"
    );

    $stmt->execute([
        $status,
        $sos_id
    ]);

    header("Location: admin_sos.php");
    exit();
}

$stmt = $pdo->query(
    "SELECT *
     FROM SOS_Request
     ORDER BY SOS_ID DESC"
);

$requests = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOS Request Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>SOS Request Management</h1>

    <?php if (count($requests) > 0): ?>

        <table border="1" cellpadding="10">

            <tr>
                <th>SOS ID</th>
                <th>Student ID</th>
                <th>Emergency Type</th>
                <th>Request Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php foreach ($requests as $request): ?>

                <tr>

                    <td><?= htmlspecialchars($request['SOS_ID']) ?></td>

                    <td><?= htmlspecialchars($request['Student_ID']) ?></td>

                    <td><?= htmlspecialchars($request['Emergency_Type']) ?></td>

                    <td><?= htmlspecialchars($request['Request_Time']) ?></td>

                    <td><?= htmlspecialchars($request['Status']) ?></td>

                    <td>

                        <form method="POST">

                            <input
                                type="hidden"
                                name="sos_id"
                                value="<?= $request['SOS_ID'] ?>"
                            >

                            <select name="status">

                                <option value="Pending"
                                    <?= $request['Status'] == 'Pending' ? 'selected' : '' ?>>
                                    Pending
                                </option>

                                <option value="In Progress"
                                    <?= $request['Status'] == 'In Progress' ? 'selected' : '' ?>>
                                    In Progress
                                </option>

                                <option value="Resolved"
                                    <?= $request['Status'] == 'Resolved' ? 'selected' : '' ?>>
                                    Resolved
                                </option>

                            </select>

                            <button type="submit">
                                Update
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    <?php else: ?>

        <p>No SOS requests found.</p>

    <?php endif; ?>

</div>

</body>
</html>