<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../LoginPage.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../AccountPage.php");
    exit();
}

$username        = trim($_POST['username']);
$email           = trim($_POST['email']);
$password        = $_POST['psw'];
$passwordConfirm = $_POST['psw-confirm'];
$userId          = (int) $_SESSION['user_id'];

if (empty($username) || empty($email) || empty($password) || empty($passwordConfirm)) {
    die("All fields are required.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format.");
}

if ($password !== $passwordConfirm) {
    die("Passwords do not match.");
}

require_once '../config/db.php';

$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $conn->prepare("UPDATE Users SET Username = ?, Email = ?, Password = ? WHERE User_ID = ?");
$stmt->bind_param("sssi", $username, $email, $hashedPassword, $userId);

if (!$stmt->execute()) {
    die("Update failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

// Keep session in sync with the new username
$_SESSION['username'] = $username;

header("Location: ../AccountPage.php");
exit();
