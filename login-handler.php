<?php
session_start();
require_once 'db_connect.php';

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    $_SESSION['error_message'] = "Email and password are required.";
    header('Location: login.php');
    exit;
}

// Prepare to fetch the user from the database
$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify the password against the stored hash
    if (password_verify($password, $user['password'])) {

        
        // Password is correct, so create the session
        session_regenerate_id(true); // Protect against session fixation

        // Store the user data in the session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['username']; // <-- THIS IS THE FIX

        // Redirect to the admin dashboard
        header('Location: admin.php');
        exit;

    } else {
        // Incorrect password
        $_SESSION['error_message'] = "Invalid email or password.";
        header('Location: login.php');
        exit;
    }
} else {
    // User with that email not found
    $_SESSION['error_message'] = "Invalid email or password.";
    header('Location: login.php');
    exit;
}

$stmt->close();
$conn->close();
?>