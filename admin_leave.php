<?php

require 'includes/session_check.php';
require 'config/db.php';

if (isset($_GET['approve'])) {

    $id = $_GET['approve'];

    $stmt = $pdo->prepare(
        "UPDATE Leave_Request
         SET Status = 'Approved'
         WHERE Request_ID = ?"
    );

    $stmt->execute([$id]);
}

if (isset($_GET['reject'])) {

    $id = $_GET['reject'];

    $stmt = $pdo->prepare(
        "UPDATE Leave_Request
         SET Status = 'Rejected'
         WHERE Request_ID = ?"
    );

    $stmt->execute([$id]);
}

$stmt = $pdo->query(
    "SELECT *
     FROM Leave_Request
     ORDER BY Request_ID DESC"
);

$requests = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Leave Requests</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Manage Leave Requests</h1>

    <table border="1" cellpadding="10">

        <tr>
            <th>Request ID</th>
            <th>Student ID</th>
            <th>Leave Date</th>
            <th>Return Date</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php foreach ($requests as $request): ?>

        <tr>

            <td>
                <?= htmlspecialchars($request['Request_ID']) ?>
            </td>

            <td>
                <?= htmlspecialchars($request['Student_ID']) ?>
            </td>

            <td>
                <?= htmlspecialchars($request['Leave_Date']) ?>
            </td>

            <td>
                <?= htmlspecialchars($request['Return_Date']) ?>
            </td>

            <td>
                <?= htmlspecialchars($request['Reason']) ?>
            </td>

            <td>
                <?= htmlspecialchars($request['Status']) ?>
            </td>

            <td>

                <a href="admin_leave.php?approve=<?= $request['Request_ID'] ?>">
                    Approve
                </a>

                |

                <a href="admin_leave.php?reject=<?= $request['Request_ID'] ?>">
                    Reject
                </a>

            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>

</body>
</html>