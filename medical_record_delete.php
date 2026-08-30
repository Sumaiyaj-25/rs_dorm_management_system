<?php

require 'includes/session_check.php';
require 'config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare(
    "DELETE FROM Medical_Record
     WHERE R_ID = ?"
);

$stmt->execute([$id]);

header("Location: medical_records.php");
exit;