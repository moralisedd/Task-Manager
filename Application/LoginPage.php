<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Task Manager</title>
    <link rel="icon" type="image/webp" href="Assets/TSM-Favicon.webp" />
    <link rel="stylesheet" href="CSS/Style.css" />
</head>

<body class="LSP-body">
    <div class="LSP-main-container">
        <main>
            <div class="username-container">
                <div class="Form-box">
                    <form action="process/login.php" method="post">
                        <div class="imgcontainer">
                            <img src="Assets/Login Icon.webp" alt="Avatar" class="avatar">
                        </div>

                        <label class="form-headers" for="uname"><b>Username / Email:</b></label>
                        <input type="text" placeholder="Enter Username or Email" name="uname" required>

                        <label class="form-headers" for="psw"><b>Password:</b></label>
                        <input type="password" placeholder="Enter Password" name="psw" required>

                        <button type="submit">Login</button>
                        <label id="Remember-text">
                            <input type="checkbox" checked="checked" name="remember"> Remember me
                        </label>
                    </form>
                </div>

                <div class="Register-container">
                    <a href="SignUpPage.php">Register</a>
                </div>

                <?php
                if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials') {
                    echo '<p style="color: red;">Invalid username, email, or password. Please try again.</p>';
                }
                ?>
            </div>
        </main>
    </div>
</body>

</html>
