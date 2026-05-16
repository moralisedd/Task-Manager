<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../LoginPage.php");
    exit();
}

require_once '../config/db.php';

$input    = trim($_POST['uname'] ?? '');
$password = $_POST['psw'] ?? '';

if (empty($input) || empty($password)) {
    header("Location: ../LoginPage.php?error=invalid_credentials");
    exit();
}

// Detect lookup field from input format rather than running two separate queries
$field = str_contains($input, '@') ? 'Email' : 'Username';

$stmt = $conn->prepare("SELECT User_ID, Username, Password, Role, Admin FROM Users WHERE $field = ?");
$stmt->bind_param("s", $input);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

// Both "user not found" and "wrong password" collapse to the same redirect -- no info leakage
if ($user && password_verify($password, $user['Password'])) {
    $_SESSION['user_id']  = $user['User_ID'];
    $_SESSION['username'] = $user['Username'];
    $_SESSION['role']     = $user['Role'];
    $_SESSION['admin']    = $user['Admin'];

    header("Location: ../HomePage.php");
    exit();
}

header("Location: ../LoginPage.php?error=invalid_credentials");
exit();
