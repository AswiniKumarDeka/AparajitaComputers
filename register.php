<?php
// show real errors (good for debugging – remove in production)
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

    // hash password (bcrypt)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        // insert into users table
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role) 
             VALUES (:name, :email, :password, 'user')"
        );
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $hashedPassword
        ]);

        // optional: redirect to login
        header("Location: login.php?registered=1");
        exit;

    } catch (PDOException $e) {
    echo "<pre style='color:red;'>";
    echo "Registration failed: " . $e->getMessage();
    echo "</pre>";
    exit;
}

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
</head>
<body>
  <h2>Create an Account</h2>
  <form method="post" action="register.php">
      <label>Username</label>
      <input type="text" name="username" required><br><br>

      <label>Email</label>
      <input type="email" name="email" required><br><br>

      <label>Password</label>
      <input type="password" name="password" required><br><br>

      <button type="submit">Register</button>
  </form>
  <p>Already have an account? <a href="login.php">Sign in here</a></p>
</body>
</html>
