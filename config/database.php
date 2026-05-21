<?php

$host = "sql211.infinityfree.com";
$db   = "if0_41983342_webcl";
$user = "if0_41983342";
$pass = "aMaltRl2JZmH2Sd";

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
