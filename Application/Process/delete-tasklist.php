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

$taskListId = (int) $_POST['tasklist_id'];
$userId     = (int) $_SESSION['user_id'];

// Verify the list belongs to this user before deleting
$checkStmt = $conn->prepare("SELECT User_ID, CollabPage_ID FROM TaskList WHERE TaskList_ID = ?");
$checkStmt->bind_param("i", $taskListId);
$checkStmt->execute();
$checkStmt->bind_result($ownerId, $collabPageId);
$checkStmt->fetch();
$checkStmt->close();

if ((int) $ownerId !== $userId) {
    http_response_code(403);
    die("You do not have permission to delete this list.");
}

$redirectTo = $collabPageId ? '../CollaborationPage.php' : '../HomePage.php';

// Tasks deleted automatically via ON DELETE CASCADE on AssignedTaskList FK
$stmt = $conn->prepare("DELETE FROM TaskList WHERE TaskList_ID = ?");
$stmt->bind_param("i", $taskListId);

if (!$stmt->execute()) {
    die("Failed to delete task list: " . $stmt->error);
}

$stmt->close();
$conn->close();

header("Location: $redirectTo");
exit();
