<?php

echo "1. PHP started<br>";
flush();

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=3306;dbname=dorm_management",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3
        ]
    );

    echo "2. DATABASE CONNECTION WORKS";

} catch (PDOException $e) {
    echo "3. DATABASE ERROR: " . $e->getMessage();
}
?>