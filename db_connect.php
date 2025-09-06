<?php
$dsn = getenv('DATABASE_URL'); // Render provides this automatically
if (!$dsn) {
    // local dev
    $dsn = 'pgsql:host=localhost;port=5432;dbname=mydb';
    $user = 'postgres';
    $pass = 'secret';
} else {
    // parse DATABASE_URL for Render
    $db = parse_url($dsn);
    $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s",
        $db['host'],
        $db['port'],
        ltrim($db['path'], '/')
    );
    $user = $db['user'];
    $pass = $db['pass'];
}

$conn = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
