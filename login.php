<?php
// Assume $pdo is your database connection

// 1. Get username and password from the form
$username = $_POST['username'];
$password_from_form = $_POST['password'];

// 2. Prepare and execute the query to find the USERNAME
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Verify the user and the password
if ($user && password_verify($password_from_form, $user['password'])) {
    
    // SUCCESS! Password is correct.
    // Start a session, redirect the user, etc.
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    header("Location: /dashboard.php"); // or wherever you want to send them
    exit();

} else {
    // FAILED!
    // Either the username was not found or the password was incorrect.
    echo "Invalid username or password.";
}
?>
