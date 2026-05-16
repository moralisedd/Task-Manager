<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php");
    exit();
}
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

<body class="Terms-Body">
    <div class="Settings-main-container">
        <main>
            <div class="Topbar-stuff">
                <div class="s-back-arrow">
                    <a href="HomePage.php">
                        <img src="Assets/Back Arrow.webp" alt="Back">
                    </a>
                </div>
                <div class="HeaderTitle">
                    <h1>SETTINGS</h1>
                </div>
            </div>

            <div class="Account-container">
                <div class="Setting-sub-header">
                    <h2>ACCOUNT</h2>
                </div>
                <div class="Go-Arrow">
                    <a href="AccountPage.php">
                        <img src="Assets/Foward Arrow.webp" width="100%" height="100%">
                    </a>
                </div>
            </div>

            <div class="Privacy-container">
                <div class="Setting-sub-header">
                    <h2>PRIVACY</h2>
                </div>
                <div class="Go-Arrow">
                    <a href="Settings-Terms&amp;PrivacyPage.php">
                        <img src="Assets/Foward Arrow.webp" width="100%" height="100%">
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
