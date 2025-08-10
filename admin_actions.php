<?php
// Get the database URL from Render's environment variables
$database_url = getenv('DATABASE_URL');

if ($database_url === false) {
    die("Error: DATABASE_URL environment variable not set.");
}

// Parse the URL into its components
$url_parts = parse_url($database_url);

$host = $url_parts['host'];
$port = $url_parts['port'];
$dbname = ltrim($url_parts['path'], '/');
$user = $url_parts['user'];
$password = $url_parts['pass'];

// Create the DSN (Data Source Name) string for PDO
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn);

    // Set the error mode to throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // If connection fails, show the error
    die("Database connection failed: " . $e->getMessage());
}
?>
