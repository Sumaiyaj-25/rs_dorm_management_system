<?php

require 'config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare(
    "DELETE FROM Staff
     WHERE Staff_ID = ?"
);

$stmt->execute([$id]);

header('Location: staff_list.php');
exit;

?>