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

require_once '../config/db.php';

$destinationPageId = (int) ($_POST['collab_page_id'] ?? 0);
$senderId          = (int) $_SESSION['user_id'];

// Verify the sender is actually the host of this collab page
$hostCheck = $conn->prepare("SELECT Host FROM CollabPage WHERE CollabPage_ID = ?");
$hostCheck->bind_param("i", $destinationPageId);
$hostCheck->execute();
$hostCheck->bind_result($pageHost);
$hostCheck->fetch();
$hostCheck->close();

if ((int) $pageHost !== $senderId) {
    http_response_code(403);
    die("You do not have permission to invite users to this page.");
}

$invite        = trim($_POST['invite'] ?? '');
$canEdit       = isset($_POST['can_edit']) ? (int) $_POST['can_edit'] : 0;
$recipientType = filter_var($invite, FILTER_VALIDATE_EMAIL) ? 'Email' : 'Username';

// Sent is always TRUE on insert -- $canEdit is stored separately in UserCollaborationLink
$sent = 1;

$stmt = $conn->prepare("INSERT INTO Invite (DestinationPage_ID, Sender, Recipient, Sent) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisi", $destinationPageId, $senderId, $recipientType, $sent);

if (!$stmt->execute()) {
    die("Invite failed: " . $stmt->error);
}
$stmt->close();

// Resolve the invitee's user ID to link them to the collab page
$recipientUserId = getUserIdByInvite($invite, $conn);

if ($recipientUserId !== null) {
    // Map $canEdit to the matching permission row (1 = View Only, 2 = Can Edit)
    $permissionId = $canEdit ? 2 : 1;

    $linkStmt = $conn->prepare("INSERT INTO UserCollaborationLink (User_ID, CollabPage_ID, Permission_ID) VALUES (?, ?, ?)");
    $linkStmt->bind_param("iii", $recipientUserId, $destinationPageId, $permissionId);
    $linkStmt->execute();
    $linkStmt->close();
} else {
    // User not in the system -- invite is logged but no link is created yet
    error_log("Invite sent to unregistered user: $invite");
}

if ($recipientType === 'Email') {
    $collabPageUrl = "http://yourdomain.com/CollaborationPage.php?id=$destinationPageId";
    $subject = "You are invited to collaborate!";
    $message = "Hello, you have been invited to collaborate on a page. Click the link to join:\n$collabPageUrl";
    $headers = "From: no-reply@yourdomain.com";
    mail($invite, $subject, $message, $headers);
}

$conn->close();
header("Location: ../CollaborationPage.php");
exit();

// --

function getUserIdByInvite($invite, $conn) {
    $stmt = $conn->prepare("SELECT User_ID FROM Users WHERE Email = ? OR Username = ?");
    $stmt->bind_param("ss", $invite, $invite);
    $stmt->execute();

    $userId = null;
    $stmt->bind_result($userId);
    $stmt->fetch();
    $stmt->close();

    return $userId;
}
