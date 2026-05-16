<?php
session_start();

require_once '../config/db.php';

$username        = trim($_POST['username']);
$email           = trim($_POST['email']);
$password        = $_POST['psw'];
$password_repeat = $_POST['psw-repeat'];

if ($password !== $password_repeat) {
    header("Location: ../SignUpPage.php?error=password_mismatch");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../SignUpPage.php?error=invalid_email");
    exit();
}

// Check username uniqueness
$stmt = $conn->prepare("SELECT User_ID FROM Users WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: ../SignUpPage.php?error=username_exists");
    exit();
}
$stmt->close();

// Check email uniqueness
$stmt = $conn->prepare("SELECT User_ID FROM Users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: ../SignUpPage.php?error=email_exists");
    exit();
}
$stmt->close();

// Explicitly use bcrypt with cost 12 rather than relying on PASSWORD_DEFAULT,
// which could change in future PHP versions
$hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $conn->prepare("INSERT INTO Users (Username, Email, Password, Role) VALUES (?, ?, ?, 'Registered User')");
$stmt->bind_param("sss", $username, $email, $hashed_password);

if (!$stmt->execute()) {
    die("Registration failed: " . $stmt->error);
}

// Auto-login immediately after registration so the user lands on HomePage directly
$newUserId = $conn->insert_id;

$stmt->close();
$conn->close();

$_SESSION['user_id']  = $newUserId;
$_SESSION['username'] = $username;
$_SESSION['role']     = 'Registered User';
$_SESSION['admin']    = false;

header("Location: ../HomePage.php");
exit();
