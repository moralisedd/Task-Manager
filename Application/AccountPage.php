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
    <div class="Account-Main-Container">
        <main>
            <div class="arrow-back_and_Header-container">
                <div class="back-arrow">
                    <a href="SettingsPage.php">
                        <img src="Assets/Back Arrow.webp" alt="Back">
                    </a>
                </div>
                <div class="h3-container">
                    <h1>ACCOUNT</h1>
                </div>
            </div>

            <div class="Account-form-container">
                <div class="Form-box">
                    <div class="signup-info-container">
                        <form action="process/change-details.php" method="post">
                            <h1>Enter new details</h1>
                            <hr>

                            <label for="username"><b>Username:</b></label>
                            <input type="text" placeholder="Enter Username" name="username" required>

                            <label for="email"><b>Email:</b></label>
                            <input type="text" placeholder="Enter Email" name="email" required>

                            <label for="psw"><b>New Password</b></label>
                            <input type="password" placeholder="Enter Password" name="psw" required>

                            <label for="psw-confirm"><b>Confirm New Password</b></label>
                            <input type="password" placeholder="Repeat Password" name="psw-confirm" required>

                            <div class="clearfix">
                                <button type="submit" class="signupbtn">Update Details</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
