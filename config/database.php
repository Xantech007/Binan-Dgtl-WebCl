<?php

$host = "sql306.infinityfree.com";
$db   = "if0_41387493_webcl";
$user = "if0_41387493";
$pass = "33tfk0H7v0GeK";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {

    die("Database connection failed: " . $e->getMessage());

}

?>
