<!-- <?php
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "my_shop_db"; // Change to your database name
$port = 3307; // Default MySQL port, change if necessary

$conn = new mysqli($host, $db_user, $db_pass, $db_name, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
 -->



<?php
// db_connect.php (Production Ready)

// These lines will get the database credentials from the Render server environment.
$servername = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

// For local development with XAMPP, it will fall back to your old settings.
if (empty($servername)) {
    $servername = "localhost";
    $username = "root";
    $password = ""; // Your XAMPP password, if you have one
    $dbname = "my_shop_db";
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
