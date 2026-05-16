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

$taskId = (int) $_POST['task_id'];
$userId = (int) $_SESSION['user_id'];

// Verify ownership via the parent task list before deleting
$checkStmt = $conn->prepare("
    SELECT tl.User_ID, tl.CollabPage_ID FROM Tasks t
    JOIN TaskList tl ON t.AssignedTaskList = tl.TaskList_ID
    WHERE t.Task_ID = ?
");
$checkStmt->bind_param("i", $taskId);
$checkStmt->execute();
$checkStmt->bind_result($ownerId, $collabPageId);
$checkStmt->fetch();
$checkStmt->close();

$redirectTo = $collabPageId ? '../CollaborationPage.php' : '../HomePage.php';

if ($collabPageId) {
    $permStmt = $conn->prepare("
        SELECT Permission_ID FROM UserCollaborationLink
        WHERE User_ID = ? AND CollabPage_ID = ? AND Permission_ID >= 2
    ");
    $permStmt->bind_param("ii", $userId, (int) $collabPageId);
    $permStmt->execute();
    $hasAccess = $permStmt->get_result()->num_rows > 0;
    $permStmt->close();

    $hostStmt = $conn->prepare("SELECT Host FROM CollabPage WHERE CollabPage_ID = ?");
    $hostStmt->bind_param("i", (int) $collabPageId);
    $hostStmt->execute();
    $hostStmt->bind_result($host);
    $hostStmt->fetch();
    $hostStmt->close();

    if (!$hasAccess && (int) $host !== $userId) {
        http_response_code(403);
        die("You do not have permission to delete this task.");
    }
} elseif ((int) $ownerId !== $userId) {
    http_response_code(403);
    die("You do not have permission to delete this task.");
}

$stmt = $conn->prepare("DELETE FROM Tasks WHERE Task_ID = ?");
$stmt->bind_param("i", $taskId);

if (!$stmt->execute()) {
    die("Failed to delete task: " . $stmt->error);
}

$stmt->close();
$conn->close();

header("Location: $redirectTo");
exit();
