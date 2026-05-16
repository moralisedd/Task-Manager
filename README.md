# Task Manager System (TSM)

A PHP/MySQL task management app with personal task boards and collaboration spaces.

---

## Setup

**Requirements:** XAMPP (Apache + MySQL), PHP 8.1+

1. Clone the repo into `C:/xampp/htdocs/` so the project sits at `C:/xampp/htdocs/Task Manager/`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open [phpMyAdmin](http://localhost/phpmyadmin), create a database named `tsm`.
4. Select the `tsm` database, go to the **Import** tab, and import `Application/Database/TaskManager_Schema.sql`.
5. Open [http://localhost/Task%20Manager/Application/LoginPage.php](http://localhost/Task%20Manager/Application/LoginPage.php).
6. Use a test account below or register a new one.

**Test accounts:**

| Email | Password |
|---|---|
| admin@tsm.com | Admin123! |
| user1@tsm.com | User1pass! |
| guest@tsm.com | GuestPass1! |

> For existing installs: run only the migration block at the bottom of the schema file, not the full import.

---

## Folder Structure

```
Application/
├── Config/          Database connection (db.php)
├── Process/         Form handlers (login, signup, tasks, invite, etc.)
├── Assets/          Images, icons, fonts
├── CSS/             Global stylesheet (Style.css)
├── Scripts/         Client-side JS (Index.js)
├── Database/        SQL schema
└── *.php            Page files
```

---

## Features

**Done**
- Register, login, logout
- Personal task board: create/delete lists, add/update/delete tasks, set priority and due date
- Collaboration spaces: host invites members, sets View Only or Can Edit permissions, transfers admin
- Collab task board: Can Edit members can add and update tasks; View Only members read only
- Account detail updates (username, email, password)
- Settings and Terms & Privacy pages

**Not yet done**
- Invite emails (placeholder domain in `Process/invite.php` — needs PHPMailer or real SMTP)
- Collab page creation UI (host must be seeded manually in the DB for now)
- Password reset flow
