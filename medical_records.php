<?php

require 'includes/session_check.php';
require 'config/db.php';

$message = '';

if (isset($_GET['success'])) {
    $message = "Medical Record Added Successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $visit_date = $_POST['visit_date'];
    $student_id = $_POST['student_id'];
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $prescription = $_POST['prescription'];

    $stmt = $pdo->prepare(
        "INSERT INTO Medical_Record
        (Visit_Date, Diagnosis, Treatment, Prescription, Student_ID)
        VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $visit_date,
        $diagnosis,
        $treatment,
        $prescription,
        $student_id
    ]);

    header("Location: medical_records.php?success=1");
    exit;
}

$stmt = $pdo->query(
    "SELECT *
     FROM Medical_Record
     ORDER BY R_ID DESC"
);

$records = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Medical Records</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Medical Records</h1>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>
            Visit Date
            <input
                type="date"
                name="visit_date"
                required>
        </label>

        <br><br>

        <label>
            Student ID
            <input
                type="number"
                name="student_id"
                required>
        </label>

        <br><br>

        <label>
            Diagnosis
            <input
                type="text"
                name="diagnosis"
                required>
        </label>

        <br><br>

        <label>
            Treatment
            <textarea
                name="treatment"></textarea>
        </label>

        <br><br>

        <label>
            Prescription
            <textarea
                name="prescription"></textarea>
        </label>

        <br><br>

        <button type="submit">
            Add Medical Record
        </button>

    </form>

    <hr>

    <h2>All Medical Records</h2>

    <table border="1" cellpadding="10">

        <tr>
            <th>ID</th>
            <th>Student ID</th>
            <th>Visit Date</th>
            <th>Diagnosis</th>
            <th>Treatment</th>
            <th>Prescription</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($records as $record): ?>

        <tr>

            <td><?= htmlspecialchars($record['R_ID']) ?></td>

            <td><?= htmlspecialchars($record['Student_ID']) ?></td>

            <td><?= htmlspecialchars($record['Visit_Date']) ?></td>

            <td><?= htmlspecialchars($record['Diagnosis']) ?></td>

            <td><?= htmlspecialchars($record['Treatment']) ?></td>

            <td><?= htmlspecialchars($record['Prescription']) ?></td>

            <td>

                <a href="medical_record_edit.php?id=<?= $record['R_ID'] ?>">
                    Edit
                </a>

                |

                <a href="medical_record_delete.php?id=<?= $record['R_ID'] ?>">
                    Delete
                </a>

            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>

</body>
</html>