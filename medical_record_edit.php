<?php

require 'includes/session_check.php';
require 'config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare(
    "SELECT * FROM Medical_Record
     WHERE R_ID = ?"
);

$stmt->execute([$id]);

$record = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $visit_date = $_POST['visit_date'];
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $prescription = $_POST['prescription'];

    $stmt = $pdo->prepare(
        "UPDATE Medical_Record
         SET Visit_Date = ?,
             Diagnosis = ?,
             Treatment = ?,
             Prescription = ?
         WHERE R_ID = ?"
    );

    $stmt->execute([
        $visit_date,
        $diagnosis,
        $treatment,
        $prescription,
        $id
    ]);

    header("Location: medical_records.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Medical Record</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="page">

    <h1>Edit Medical Record</h1>

    <form method="POST">

        <label>
            Visit Date
            <input
                type="date"
                name="visit_date"
                value="<?= htmlspecialchars($record['Visit_Date']) ?>"
                required>
        </label>

        <br><br>

        <label>
            Diagnosis
            <input
                type="text"
                name="diagnosis"
                value="<?= htmlspecialchars($record['Diagnosis']) ?>"
                required>
        </label>

        <br><br>

        <label>
            Treatment
            <textarea name="treatment"><?= htmlspecialchars($record['Treatment']) ?></textarea>
        </label>

        <br><br>

        <label>
            Prescription
            <textarea name="prescription"><?= htmlspecialchars($record['Prescription']) ?></textarea>
        </label>

        <br><br>

        <button type="submit">
            Update Record
        </button>

        <a href="medical_records.php">
            Cancel
        </a>

    </form>

</div>

</body>
</html>