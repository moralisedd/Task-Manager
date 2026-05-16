<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php");
    exit();
}

require_once 'config/db.php';

$userId = $_SESSION['user_id'];

// Find the collab page this user belongs to (as host or member)
$pageStmt = $conn->prepare("
    SELECT cp.CollabPage_ID, cp.Host
    FROM CollabPage cp
    LEFT JOIN UserCollaborationLink ucl ON cp.CollabPage_ID = ucl.CollabPage_ID AND ucl.User_ID = ?
    WHERE cp.Host = ? OR ucl.User_ID = ?
    LIMIT 1
");
$pageStmt->bind_param("iii", $userId, $userId, $userId);
$pageStmt->execute();
$pageStmt->bind_result($collabPageId, $collabHost);
$pageStmt->fetch();
$pageStmt->close();

// Store in session so process files can use it without another query
if ($collabPageId) {
    $_SESSION['collab_page_id'] = $collabPageId;
}

// Cast both sides to int to avoid type mismatch between DB int and session mixed type
$isHost  = ((int) $collabHost === (int) $userId);
$canEdit = $isHost;

if (!$isHost && $collabPageId) {
    $permStmt = $conn->prepare("
        SELECT Permission_ID FROM UserCollaborationLink
        WHERE User_ID = ? AND CollabPage_ID = ?
    ");
    $permStmt->bind_param("ii", $userId, $collabPageId);
    $permStmt->execute();
    $permStmt->bind_result($permissionId);
    $permStmt->fetch();
    $permStmt->close();

    // Permission 2 = Can Edit, 3 = Admin (both allow task creation)
    $canEdit = ($permissionId >= 2);
}

// Fetch collab task lists and their tasks
$taskLists = [];

if ($collabPageId) {
    $listStmt = $conn->prepare("SELECT TaskList_ID, Name FROM TaskList WHERE CollabPage_ID = ? ORDER BY TaskList_ID ASC");
    $listStmt->bind_param("i", $collabPageId);
    $listStmt->execute();
    $listsResult = $listStmt->get_result();

    while ($list = $listsResult->fetch_assoc()) {
        $taskStmt = $conn->prepare("SELECT Task_ID, Name, Due_Date, Priority, Progress FROM Tasks WHERE AssignedTaskList = ? ORDER BY Priority ASC, Due_Date ASC");
        $taskStmt->bind_param("i", $list['TaskList_ID']);
        $taskStmt->execute();
        $list['tasks'] = $taskStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $taskStmt->close();

        $taskLists[] = $list;
    }
    $listStmt->close();
}

// Only fetch users for the transfer dropdown when the current user is the host
$allUsers = [];
if ($isHost) {
    $usersResult = $conn->query("SELECT User_ID, Username FROM Users");
    $allUsers = $usersResult->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Task Manager</title>
    <link rel="icon" type="image/webp" href="Assets/TSM-Favicon.webp" />
    <link rel="stylesheet" href="CSS/Style.css" />
</head>

<body>
    <div class="main-container">
        <sidebar>
            <nav>
                <div class="flex-container">
                    <button id="open-and-close-sidebar">
                        <img src="Assets/arrow-back.svg" alt="icon" height="30" width="20" />
                    </button>
                    <div class="Personalpage-icon">
                        <a href="HomePage.php">
                            <img src="Assets/Personal Page Icon.webp" alt="Personal Page Logo" height="100%" width="100%">
                        </a>
                    </div>
                    <div class="Collabpage-icon">
                        <a href="CollaborationPage.php">
                            <img src="Assets/Group Collab Icon.webp" alt="Collaboration Page Logo" height="100%" width="100%">
                        </a>
                    </div>
                    <div class="Exitpage-icon">
                        <a href="process/logout.php">
                            <img src="Assets/Exit Page Icon.webp" alt="Exit Page Logo" height="100%" width="100%">
                        </a>
                    </div>
                </div>
            </nav>
        </sidebar>

        <main class="tsm-main">
            <div>
                <div class="logo-and-settings">
                    <div class="TSM-icon">
                        <img src="Assets/Collaboration Logo.webp" alt="Collaboration Logo">
                    </div>
                    <div class="Settings-icon">
                        <a href="SettingsPage.php">
                            <img src="Assets/Settings Gear Icon.webp" alt="Settings Icon">
                        </a>
                    </div>
                    <?php if ($isHost): ?>
                    <div class="Share-icon" id="share-icon">
                        <img src="Assets/Share Icon.webp" alt="Share Icon">
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($isHost): ?>
                <div class="share-container" id="share-container">
                    <div>
                        <h3>Share</h3>
                    </div>
                    <div>
                        <form action="process/invite.php" method="post">
                            <input type="hidden" name="collab_page_id" value="<?= $collabPageId ?>">
                            <input type="text" id="invite" name="invite" placeholder="Add usernames or emails..."><br>
                            <input type="hidden" name="can_edit" id="can_edit" value="0">
                            <input type="submit" value="Invite">
                        </form>
                    </div>
                    <div class="permissions-container">
                        <h3>Permissions</h3>
                        <div class="switch-container">
                            <p id="can-edit-text">Can Edit</p>
                            <label class="switch">
                                <input type="checkbox" id="can_edit_checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <form action="process/transfer.php" method="post">
                            <label for="admin" id="transfer-admin-text">Transfer Admin</label>
                            <select id="admin" name="admin">
                                <?php foreach ($allUsers as $user): ?>
                                    <?php if ($user['User_ID'] !== $userId): ?>
                                    <option value="<?= htmlspecialchars($user['User_ID']) ?>">
                                        <?= htmlspecialchars($user['Username']) ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <input type="submit" value="Submit">
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!$collabPageId): ?>
                <div class="collab-empty">
                    <p>You are not part of any collaboration space yet. Ask a host to invite you.</p>
                </div>
                <?php else: ?>
                <div class="task-board">
                    <?php foreach ($taskLists as $list): ?>
                    <div class="task-column">
                        <div class="column-header">
                            <h3><?= htmlspecialchars($list['Name']) ?></h3>
                            <?php if ($canEdit): ?>
                            <div class="column-actions">
                                <button class="add-task-btn" data-list="<?= $list['TaskList_ID'] ?>" title="Add task">+</button>
                                <?php if ($isHost): ?>
                                <form action="process/delete-tasklist.php" method="post" class="inline-form" onsubmit="return confirm('Delete this list and all its tasks?')">
                                    <input type="hidden" name="tasklist_id" value="<?= $list['TaskList_ID'] ?>">
                                    <button type="submit" class="delete-list-btn" title="Delete list">
                                        <img src="Assets/Delete Icon.webp" alt="Delete" height="18" width="18">
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="task-cards">
                            <?php if (empty($list['tasks'])): ?>
                            <p class="empty-list-msg">No tasks yet.</p>
                            <?php else: ?>
                                <?php foreach ($list['tasks'] as $task): ?>
                                <div class="task-card priority-<?= strtolower($task['Priority']) ?>">
                                    <div class="task-name"><?= htmlspecialchars($task['Name']) ?></div>
                                    <div class="task-meta">
                                        <span class="task-due">Due: <?= htmlspecialchars($task['Due_Date']) ?></span>
                                        <span class="task-priority badge-<?= strtolower($task['Priority']) ?>"><?= $task['Priority'] ?></span>
                                    </div>
                                    <div class="task-footer">
                                        <?php if ($canEdit): ?>
                                        <form action="process/update-task.php" method="post">
                                            <input type="hidden" name="task_id" value="<?= $task['Task_ID'] ?>">
                                            <select name="progress" onchange="this.form.submit()" class="progress-select status-<?= strtolower(str_replace(' ', '-', $task['Progress'])) ?>">
                                                <option value="Not Started" <?= $task['Progress'] === 'Not Started' ? 'selected' : '' ?>>Not Started</option>
                                                <option value="In Progress" <?= $task['Progress'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="Completed"   <?= $task['Progress'] === 'Completed'   ? 'selected' : '' ?>>Completed</option>
                                            </select>
                                        </form>
                                        <form action="process/delete-task.php" method="post" class="inline-form" onsubmit="return confirm('Delete this task?')">
                                            <input type="hidden" name="task_id" value="<?= $task['Task_ID'] ?>">
                                            <button type="submit" class="delete-task-btn" title="Delete task">
                                                <img src="Assets/Delete Icon.webp" alt="Delete" height="16" width="16">
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="progress-label status-<?= strtolower(str_replace(' ', '-', $task['Progress'])) ?>"><?= $task['Progress'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($canEdit): ?>
                        <div class="add-task-form" id="add-form-<?= $list['TaskList_ID'] ?>">
                            <form action="process/create-task.php" method="post">
                                <input type="hidden" name="tasklist_id" value="<?= $list['TaskList_ID'] ?>">
                                <input type="text" name="name" placeholder="Task name" required>
                                <input type="date" name="due_date" required>
                                <select name="priority">
                                    <option value="Low">Low</option>
                                    <option value="Important">Important</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                                <select name="progress">
                                    <option value="Not Started">Not Started</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                </select>
                                <div class="add-task-form-actions">
                                    <button type="submit">Add Task</button>
                                    <button type="button" class="cancel-task-btn" data-list="<?= $list['TaskList_ID'] ?>">Cancel</button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <?php if ($isHost): ?>
                    <div class="new-list-column">
                        <button id="new-list-btn">
                            <img src="Assets/New Task List Icon.webp" alt="New List" height="40" width="40">
                            <span>New List</span>
                        </button>
                        <div id="new-list-form">
                            <form action="process/create-tasklist.php" method="post">
                                <input type="hidden" name="collab_page_id" value="<?= $collabPageId ?>">
                                <input type="text" name="name" placeholder="List name" required>
                                <div class="add-task-form-actions">
                                    <button type="submit">Create</button>
                                    <button type="button" id="cancel-new-list">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="Scripts/Index.js"></script>
</body>

</html>
