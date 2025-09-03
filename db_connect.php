<?php
$host     = "d2s3c1u3jp1c738qktg0-a.oregon-postgres.render.com"; // full host
$port     = "5432";
$dbname   = "aparajita_db";
$user     = "aparajita_db_user";
$password = "your-long-password";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // echo "Connected!";
} catch (PDOException $e) {
    echo "DB connect error: " . $e->getMessage();
    exit;
}
