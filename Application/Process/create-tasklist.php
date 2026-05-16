<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../LoginPage.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../HomePage.php");
    exit();
}

require_once '../config/db.php';

$name          = trim($_POST['name']);
$userId        = (int) $_SESSION['user_id'];
$collabPageId  = isset($_POST['collab_page_id']) ? (int) $_POST['collab_page_id'] : null;
$redirectTo    = $collabPageId ? '../CollaborationPage.php' : '../HomePage.php';

if (empty($name)) {
    header("Location: $redirectTo?error=empty_name");
    exit();
}

if ($collabPageId !== null) {
    $stmt = $conn->prepare("INSERT INTO TaskList (Name, User_ID, CollabPage_ID) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $name, $userId, $collabPageId);
} else {
    $stmt = $conn->prepare("INSERT INTO TaskList (Name, User_ID) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $userId);
}

if (!$stmt->execute()) {
    die("Failed to create task list: " . $stmt->error);
}

$stmt->close();
$conn->close();

header("Location: $redirectTo");
exit();
