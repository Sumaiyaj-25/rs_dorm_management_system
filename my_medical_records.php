<?php

require 'includes/session_check.php';
require 'config/db.php';

$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare(
    "SELECT *
     FROM Medical_Record
     WHERE Student_ID = ?
     ORDER BY R_ID DESC"
);

$stmt->execute([$student_id]);

$records = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>My Medical Records</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>My Medical Records</h1>

    <?php if (count($records) > 0): ?>

        <table border="1" cellpadding="10">

            <tr>

                <th>ID</th>

                <th>Visit Date</th>

                <th>Diagnosis</th>

                <th>Treatment</th>

                <th>Prescription</th>

            </tr>

            <?php foreach ($records as $record): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($record['R_ID']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($record['Visit_Date']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($record['Diagnosis']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($record['Treatment']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($record['Prescription']) ?>
                </td>

            </tr>

            <?php endforeach; ?>

        </table>

    <?php else: ?>

        <p>No medical records found.</p>

    <?php endif; ?>

</div>

</body>

</html>