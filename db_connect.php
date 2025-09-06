<?php
// db_connect.php — Render-friendly PDO Postgres connection

// Turn on strict errors in dev; hide notices in prod
error_reporting(E_ALL);
ini_set('display_errors', 0);

$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) {
    // local fallback
    $host = 'localhost';
    $port = 5432;
    $db   = 'aparajita_db';
    $user = 'postgres';
    $pass = 'postgres';
    $dsn  = "pgsql:host=$host;port=$port;dbname=$db";
} else {
    // parse Render DATABASE_URL
    $parts = parse_url($databaseUrl);
    $host  = $parts['host'];
    $port  = $parts['port'] ?? 5432;
    $db    = ltrim($parts['path'], '/');
    $user  = $parts['user'];
    $pass  = $parts['pass'];
    $dsn   = "pgsql:host=$host;port=$port;dbname=$db";
}

try {
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    // Return JSON error immediately
    header('Content-Type: application/json');
    echo json_encode(['error' => 'DB connect error: ' . $e->getMessage()]);
    exit;
}
