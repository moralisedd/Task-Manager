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
    <div class="Terms-Main-Container">
        <main>
            <div class="arrow-back_and_Header-container">
                <div class="back-arrow">
                    <a href="SettingsPage.php">
                        <img src="Assets/Back Arrow.webp" alt="Back">
                    </a>
                </div>
                <div class="h3-container">
                    <h1>Terms &amp; Privacy</h1>
                </div>
            </div>

            <div class="PrivacyInfo-container">
                <div class="P-text">
                    <p>
                        This Privacy Policy (the "Policy") explains how Sheffield Hallam University ("we," "us," or
                        "our") collects, uses, and discloses information from and about users ("you" or "your") of our
                        task management system (the "System").
                    </p>
                    <h3>Information We Collect</h3>
                    <p>We collect the following information from and about you:</p>
                    <ul>
                        <li>Account Information: When you create an account with the System, we collect your name, username, email address, and password (hashed and salted for security).</li>
                        <li>Usage Data: We collect information about how you use the System, such as the tasks you create and manage, notes you take, and features you access.</li>
                        <li>Device Information: We collect information about the device you use to access the System, such as the device type, operating system, IP address, and browser type.</li>
                        <li>Log Data: We automatically collect log data when you use the System, such as access times, pages viewed, and search queries.</li>
                    </ul>

                    <h3>How We Use Your Information</h3>
                    <p>We use the information we collect for the following purposes:</p>
                    <ul>
                        <li>To provide and operate the System</li>
                        <li>To create and manage your account</li>
                        <li>To personalise your experience with the System</li>
                        <li>To analyse how the System is used and improve our services</li>
                        <li>To send you important information about the System, such as updates, security alerts, and support messages</li>
                        <li>To comply with legal and regulatory requirements</li>
                    </ul>

                    <h3>Legal Bases for Processing</h3>
                    <ul>
                        <li>Contract: We process your information to fulfil our contractual obligations to you, such as providing access to the System.</li>
                        <li>Legitimate Interests: We process your information for our legitimate interests, such as improving the System and providing you with a personalised experience.</li>
                        <li>Consent: We may process your information for certain purposes with your consent, such as sending you marketing communications.</li>
                    </ul>

                    <h3>Your Rights</h3>
                    <ul>
                        <li>Right to access: You have the right to access and receive a copy of the information we hold about you.</li>
                        <li>Right to rectification: You have the right to request that we rectify any inaccurate information we hold about you.</li>
                        <li>Right to erasure: You have the right to request that we erase your personal information.</li>
                        <li>Right to restrict processing: You have the right to restrict the processing of your personal information.</li>
                        <li>Right to object: You have the right to object to the processing of your personal information.</li>
                        <li>Right to data portability: You have the right to request that we transfer your personal information to another controller.</li>
                    </ul>

                    <br>
                    <h3>Data Retention</h3>
                    <p>We will retain your information for as long as necessary to fulfil the purposes described in this Policy, unless a longer retention period is required or permitted by law.</p>

                    <br>
                    <h3>Security</h3>
                    <p>We take reasonable steps to protect your information from unauthorised access, disclosure, alteration, or destruction. However, no security measures are perfect, and we cannot guarantee the security of your information.</p>

                    <br>
                    <h3>Third-Party Services</h3>
                    <p>We may share your information with third-party service providers who help us operate the System and provide you with the services you request. These providers are obligated to comply with this Policy and applicable data protection laws.</p>

                    <br>
                    <h3>International Transfers</h3>
                    <p>We may transfer your information to countries outside the United Kingdom. We will only transfer your information to countries approved by the UK Information Commissioner's Office (ICO) as having adequate data protection laws, or we will put in place appropriate safeguards.</p>

                    <br>
                    <h3>Children's Privacy</h3>
                    <p>The System is not intended for children under the age of 13. We do not knowingly collect personal information from children under 13.</p>

                    <br>
                    <h3>Changes to this Policy</h3>
                    <p>We may update this Policy from time to time. We will notify you of any changes by posting the new Policy on this page.</p>

                    <br>
                    <h3>Effective Date</h3>
                    <p>This Policy is effective as of 25/04/2024.</p>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
