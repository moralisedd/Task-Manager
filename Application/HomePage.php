<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php");
    exit();
}

require_once 'config/db.php';

$userId = $_SESSION['user_id'];

// Fetch all personal task lists for this user (CollabPage_ID IS NULL = personal)
$listStmt = $conn->prepare("SELECT TaskList_ID, Name FROM TaskList WHERE User_ID = ? AND CollabPage_ID IS NULL ORDER BY TaskList_ID ASC");
$listStmt->bind_param("i", $userId);
$listStmt->execute();
$listsResult = $listStmt->get_result();

$taskLists = [];
while ($list = $listsResult->fetch_assoc()) {
    // Fetch tasks for each list
    $taskStmt = $conn->prepare("SELECT Task_ID, Name, Due_Date, Priority, Progress FROM Tasks WHERE AssignedTaskList = ? ORDER BY Priority ASC, Due_Date ASC");
    $taskStmt->bind_param("i", $list['TaskList_ID']);
    $taskStmt->execute();
    $list['tasks'] = $taskStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $taskStmt->close();

    $taskLists[] = $list;
}

$listStmt->close();
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
            <div class="logo-and-settings">
                <div class="TSM-icon">
                    <img src="Assets/TaskMainPageLogo.webp" alt="Task Manager Icon" height="100%" width="100%">
                </div>
                <div class="Settings-icon">
                    <a href="SettingsPage.php">
                        <img src="Assets/Settings Gear Icon.webp" alt="Settings Icon" height="100%" width="100%">
                    </a>
                </div>
            </div>

            <div class="task-board">
                <?php foreach ($taskLists as $list): ?>
                <div class="task-column">
                    <div class="column-header">
                        <h3><?= htmlspecialchars($list['Name']) ?></h3>
                        <div class="column-actions">
                            <button class="add-task-btn" data-list="<?= $list['TaskList_ID'] ?>" title="Add task">+</button>
                            <form action="process/delete-tasklist.php" method="post" class="inline-form" onsubmit="return confirm('Delete this list and all its tasks?')">
                                <input type="hidden" name="tasklist_id" value="<?= $list['TaskList_ID'] ?>">
                                <button type="submit" class="delete-list-btn" title="Delete list">
                                    <img src="Assets/Delete Icon.webp" alt="Delete List" height="18" width="18">
                                </button>
                            </form>
                        </div>
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
                                    <form action="process/update-task.php" method="post">
                                        <input type="hidden" name="task_id" value="<?= $task['Task_ID'] ?>">
                                        <select name="progress" onchange="this.form.submit()" class="progress-select status-<?= strtolower(str_replace(' ', '-', $task['Progress'])) ?>">
                                            <option value="Not Started"  <?= $task['Progress'] === 'Not Started'  ? 'selected' : '' ?>>Not Started</option>
                                            <option value="In Progress"  <?= $task['Progress'] === 'In Progress'  ? 'selected' : '' ?>>In Progress</option>
                                            <option value="Completed"    <?= $task['Progress'] === 'Completed'    ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </form>
                                    <form action="process/delete-task.php" method="post" class="inline-form" onsubmit="return confirm('Delete this task?')">
                                        <input type="hidden" name="task_id" value="<?= $task['Task_ID'] ?>">
                                        <button type="submit" class="delete-task-btn" title="Delete task">
                                            <img src="Assets/Delete Icon.webp" alt="Delete" height="16" width="16">
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

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
                </div>
                <?php endforeach; ?>

                <div class="new-list-column">
                    <button id="new-list-btn">
                        <img src="Assets/New Task List Icon.webp" alt="New List" height="40" width="40">
                        <span>New List</span>
                    </button>
                    <div id="new-list-form">
                        <form action="process/create-tasklist.php" method="post">
                            <input type="text" name="name" placeholder="List name" required>
                            <div class="add-task-form-actions">
                                <button type="submit">Create</button>
                                <button type="button" id="cancel-new-list">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="Scripts/Index.js"></script>
</body>

</html>
