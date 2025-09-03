
<?php
$host     = "d2s3c1u3jp1c738qktg0-a.oregon-postgres.render.com";
$port     = "5432";
$dbname   = "aparajita_db";
$user     = "aparajita_db_user";
$password = "yiDKShPoESiyS151fldPy1QJIZVZ8LG1";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB connect error: " . $e->getMessage());
}
