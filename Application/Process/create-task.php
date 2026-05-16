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
$name       = trim($_POST['name']);
$dueDate    = $_POST['due_date'];
$priority   = $_POST['priority'];
$progress   = $_POST['progress'] ?? 'Not Started';
$userId     = (int) $_SESSION['user_id'];

// Validate ENUM values before hitting the DB -- prevents garbage data and silent truncation
$allowedPriorities = ['Urgent', 'Important', 'Low'];
$allowedProgress   = ['Not Started', 'In Progress', 'Completed'];

if (empty($name) || !in_array($priority, $allowedPriorities, true) || !in_array($progress, $allowedProgress, true)) {
    header("Location: ../HomePage.php?error=invalid_input");
    exit();
}

// Validate date format
$dateObj = DateTime::createFromFormat('Y-m-d', $dueDate);
if (!$dateObj || $dateObj->format('Y-m-d') !== $dueDate) {
    header("Location: ../HomePage.php?error=invalid_date");
    exit();
}

// Verify the task list exists and check ownership/permission
$checkStmt = $conn->prepare("SELECT User_ID, CollabPage_ID FROM TaskList WHERE TaskList_ID = ?");
$checkStmt->bind_param("i", $taskListId);
$checkStmt->execute();
$checkStmt->bind_result($ownerId, $collabPageId);
$checkStmt->fetch();
$checkStmt->close();

$redirectTo = $collabPageId ? '../CollaborationPage.php' : '../HomePage.php';

if ($collabPageId) {
    // Check if user has Can Edit (2) or Admin (3) permission on the collab page
    $permStmt = $conn->prepare("
        SELECT Permission_ID FROM UserCollaborationLink
        WHERE User_ID = ? AND CollabPage_ID = ? AND Permission_ID >= 2
    ");
    $permStmt->bind_param("ii", $userId, (int) $collabPageId);
    $permStmt->execute();
    $hasAccess = $permStmt->get_result()->num_rows > 0;
    $permStmt->close();

    // Host is always allowed even if not in UserCollaborationLink
    $hostStmt = $conn->prepare("SELECT Host FROM CollabPage WHERE CollabPage_ID = ?");
    $hostStmt->bind_param("i", (int) $collabPageId);
    $hostStmt->execute();
    $hostStmt->bind_result($host);
    $hostStmt->fetch();
    $hostStmt->close();

    if (!$hasAccess && (int) $host !== $userId) {
        http_response_code(403);
        die("You do not have permission to add tasks to this list.");
    }
} elseif ((int) $ownerId !== $userId) {
    http_response_code(403);
    die("You do not have permission to add tasks to this list.");
}

$stmt = $conn->prepare("INSERT INTO Tasks (AssignedTaskList, Author, Name, Due_Date, Priority, Progress) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissss", $taskListId, $userId, $name, $dueDate, $priority, $progress);

if (!$stmt->execute()) {
    die("Failed to create task: " . $stmt->error);
}

$stmt->close();
$conn->close();

header("Location: $redirectTo");
exit();
