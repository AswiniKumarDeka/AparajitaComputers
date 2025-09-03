<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        echo "<p style='color:red;'>All fields are required.</p>";
        exit;
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);

    try {
        // change "name" to "username" here if that's your column
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role)
             VALUES (:name, :email, :password, 'user')"
        );
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $hashed
        ]);

        echo "<p style='color:green;'>Registration successful!</p>";
        // header("Location: login.php"); exit;

    } catch (PDOException $e) {
        echo "<pre style='color:red;'>".$e->getMessage()."</pre>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Register</title></head>
<body>
<h2>Create Account</h2>
<form method="post" action="register.php">
  <label>Username <input type="text" name="username" required></label><br><br>
  <label>Email <input type="email" name="email" required></label><br><br>
  <label>Password <input type="password" name="password" required></label><br><br>
  <button type="submit">Register</button>
</form>
<p>Already have an account? <a href="login.php">Sign in</a></p>
</body>
</html>
