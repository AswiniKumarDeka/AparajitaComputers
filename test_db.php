<?php
echo "<h1>Database Connection Test</h1>";

// Attempt to include the database configuration file.
// Make sure 'database.php' is the correct filename.
require_once 'database.php';

// Check if the $pdo variable was created successfully in that file.
if (isset($pdo)) {
    echo "<p style='color: green; font-weight: bold;'>SUCCESS: The 'database.php' file was loaded and the \$pdo connection variable exists.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>FAILURE: The 'database.php' file was loaded, but it did NOT create the \$pdo connection variable.</p>";
}
?>