<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../LoginPage.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../CollaborationPage.php");
    exit();
}

// collab_page_id is stored in session when the user visits CollaborationPage.php
if (!isset($_SESSION['collab_page_id'])) {
    header("Location: ../CollaborationPage.php");
    exit();
}

require_once '../config/db.php';

$newAdminId    = (int) $_POST['admin'];
$collabPageId  = (int) $_SESSION['collab_page_id'];
$currentUserId = (int) $_SESSION['user_id'];

// Only the current host can transfer admin -- block direct POST calls from non-hosts
$checkStmt = $conn->prepare("SELECT Host FROM CollabPage WHERE CollabPage_ID = ?");
$checkStmt->bind_param("i", $collabPageId);
$checkStmt->execute();
$checkStmt->bind_result($currentHost);
$checkStmt->fetch();
$checkStmt->close();

// Cast to int to avoid type mismatch between session string and DB int
if ((int) $currentHost !== $currentUserId) {
    http_response_code(403);
    die("You are not the host of this collaboration page.");
}

// Disable FK checks temporarily -- Invite.Sender FK blocks the Host update otherwise
$conn->query("SET FOREIGN_KEY_CHECKS=0");

$stmt = $conn->prepare("UPDATE CollabPage SET Host = ? WHERE CollabPage_ID = ?");
$stmt->bind_param("ii", $newAdminId, $collabPageId);

if (!$stmt->execute()) {
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    die("Transfer failed: " . $stmt->error);
}

$stmt->close();
$conn->query("SET FOREIGN_KEY_CHECKS=1");
$conn->close();

header("Location: ../CollaborationPage.php");
exit();
