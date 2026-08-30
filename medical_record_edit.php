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
<html>
<head>
    <title>Edit Medical Record</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1>Edit Medical Record</h1>

<form method="POST">

    <input type="date"
           name="visit_date"
           value="<?= $record['Visit_Date'] ?>"
           required>

    <br><br>

    <input type="text"
           name="diagnosis"
           value="<?= $record['Diagnosis'] ?>"
           required>

    <br><br>

    <textarea name="treatment"><?= $record['Treatment'] ?></textarea>

    <br><br>

    <textarea name="prescription"><?= $record['Prescription'] ?></textarea>

    <br><br>

    <button type="submit">
        Update Record
    </button>

</form>

</body>
</html>