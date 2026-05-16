-- Task Manager System (TSM) -- Full Schema
-- Import against a fresh 'tsm' database in phpMyAdmin or MySQL CLI.
-- For upgrades, run only the migration block at the bottom.
--
-- Test credentials (bcrypt cost 12, compatible with PHP password_verify):
--   admin@tsm.com    / Admin123!
--   user1@tsm.com    / User1pass!
--   guest@tsm.com    / GuestPass1!
-- =========================================================

-- Users Table
CREATE TABLE IF NOT EXISTS Users (
    User_ID     INTEGER      PRIMARY KEY AUTO_INCREMENT,
    Email       VARCHAR(100) NOT NULL UNIQUE,
    Username    VARCHAR(50)  NOT NULL UNIQUE,
    Password    VARCHAR(255) NOT NULL,
    Role        ENUM('Admin', 'Registered User', 'Non-Registered USER') NOT NULL,
    Admin       BOOLEAN      NOT NULL DEFAULT FALSE,
    SessionData TEXT
);

-- CollabPage must exist before TaskList references it
CREATE TABLE IF NOT EXISTS CollabPage (
    CollabPage_ID INTEGER PRIMARY KEY AUTO_INCREMENT,
    Host          INTEGER,
    FOREIGN KEY (Host) REFERENCES Users(User_ID)
);

-- TaskList Table
-- CollabPage_ID NULL = personal list; set = belongs to that collab space
CREATE TABLE IF NOT EXISTS TaskList (
    TaskList_ID   INTEGER PRIMARY KEY AUTO_INCREMENT,
    Name          TEXT    NOT NULL,
    User_ID       INTEGER NOT NULL,
    CollabPage_ID INTEGER NULL,
    FOREIGN KEY (User_ID)       REFERENCES Users(User_ID),
    FOREIGN KEY (CollabPage_ID) REFERENCES CollabPage(CollabPage_ID)
);

-- Tasks Table
CREATE TABLE IF NOT EXISTS Tasks (
    Task_ID          INTEGER     PRIMARY KEY AUTO_INCREMENT,
    AssignedTaskList INTEGER,
    Author           INTEGER,
    Name             VARCHAR(50) NOT NULL,
    Due_Date         DATE        NOT NULL,
    Priority         ENUM('Urgent', 'Important', 'Low')                   NOT NULL,
    Progress         ENUM('Not Started', 'In Progress', 'Completed')      NOT NULL DEFAULT 'Not Started',
    FOREIGN KEY (AssignedTaskList) REFERENCES TaskList(TaskList_ID) ON DELETE CASCADE,
    FOREIGN KEY (Author)           REFERENCES Users(User_ID)
);

-- Permissions Table
CREATE TABLE IF NOT EXISTS Permissions (
    Permission_ID INTEGER PRIMARY KEY AUTO_INCREMENT,
    Setting       ENUM('View Only', 'Can Edit', 'Admin') NOT NULL
);

-- UserCollaborationLink Table
CREATE TABLE IF NOT EXISTS UserCollaborationLink (
    User_ID       INTEGER,
    CollabPage_ID INTEGER,
    Permission_ID INTEGER,
    FOREIGN KEY (User_ID)       REFERENCES Users(User_ID),
    FOREIGN KEY (CollabPage_ID) REFERENCES CollabPage(CollabPage_ID) ON DELETE CASCADE,
    FOREIGN KEY (Permission_ID) REFERENCES Permissions(Permission_ID)
);

-- Invite Table
CREATE TABLE IF NOT EXISTS Invite (
    Invite_ID          INTEGER PRIMARY KEY AUTO_INCREMENT,
    DestinationPage_ID INTEGER,
    Sender             INTEGER,
    Recipient          ENUM('Email', 'Username') NOT NULL,
    Sent               BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (DestinationPage_ID) REFERENCES CollabPage(CollabPage_ID),
    FOREIGN KEY (Sender)             REFERENCES Users(User_ID)
);


-- =========================================================
-- SAMPLE DATA
-- =========================================================

-- Permissions (seed rows required by invite/collab logic)
INSERT IGNORE INTO Permissions (Permission_ID, Setting) VALUES
    (1, 'View Only'),
    (2, 'Can Edit'),
    (3, 'Admin');

-- Users (passwords hashed with bcrypt cost 12 via PHP PASSWORD_BCRYPT)
-- admin@tsm.com   -> Admin123!
-- user1@tsm.com   -> User1pass!
-- guest@tsm.com   -> GuestPass1!
INSERT INTO Users (User_ID, Email, Username, Password, Role, Admin) VALUES
    (1, 'admin@tsm.com',  'Admin',  '$2y$12$fi/9WX.GSXjIuyUDq1gYBeed.elJCmsIvanpgG0YUWgP7cD4lnJu2', 'Admin',          TRUE),
    (2, 'user1@tsm.com',  'User1',  '$2y$12$2UV7kpSFD4BLlBw1BB1Xzevo3pWkxNdfS71Qhq452y1iBNH5Dd7cu', 'Registered User', FALSE),
    (3, 'guest@tsm.com',  'Guest',  '$2y$12$bNQrvna6MNPpIgAqc9.Cr.Zq281HHJjJDiFAxJbWWu/PBc3abqYKi', 'Registered User', FALSE);

-- Collaboration Space (Admin is host)
INSERT INTO CollabPage (CollabPage_ID, Host) VALUES
    (1, 1);

-- Personal Task Lists
INSERT INTO TaskList (TaskList_ID, Name, User_ID, CollabPage_ID) VALUES
    (1, 'To-Do',       1, NULL),
    (2, 'In Progress', 1, NULL),
    (3, 'Done',        1, NULL),
    (4, 'Backlog',     2, NULL),
    (5, 'This Week',   2, NULL);

-- Collab Task Lists (linked to CollabPage 1)
INSERT INTO TaskList (TaskList_ID, Name, User_ID, CollabPage_ID) VALUES
    (6, 'Sprint Board',  1, 1),
    (7, 'Completed',     1, 1);

-- Personal Tasks (Admin)
INSERT INTO Tasks (Task_ID, AssignedTaskList, Author, Name, Due_Date, Priority, Progress) VALUES
    (1,  1, 1, 'Write project proposal',  '2025-06-01', 'Urgent',    'Not Started'),
    (2,  1, 1, 'Book team meeting room',  '2025-05-20', 'Important', 'Not Started'),
    (3,  2, 1, 'Review pull requests',    '2025-05-18', 'Urgent',    'In Progress'),
    (4,  2, 1, 'Update documentation',    '2025-05-25', 'Low',       'In Progress'),
    (5,  3, 1, 'Deploy hotfix v1.2.1',    '2025-05-10', 'Urgent',    'Completed'),
    (6,  3, 1, 'Write unit tests',        '2025-05-12', 'Important', 'Completed');

-- Personal Tasks (User1)
INSERT INTO Tasks (Task_ID, AssignedTaskList, Author, Name, Due_Date, Priority, Progress) VALUES
    (7,  4, 2, 'Read sprint backlog',     '2025-06-05', 'Low',       'Not Started'),
    (8,  4, 2, 'Set up local dev env',    '2025-05-22', 'Important', 'Not Started'),
    (9,  5, 2, 'Implement login page',    '2025-05-19', 'Urgent',    'In Progress'),
    (10, 5, 2, 'Fix CSS layout bug',      '2025-05-21', 'Important', 'In Progress');

-- Collab Tasks
INSERT INTO Tasks (Task_ID, AssignedTaskList, Author, Name, Due_Date, Priority, Progress) VALUES
    (11, 6, 1, 'Define API endpoints',    '2025-05-24', 'Urgent',    'In Progress'),
    (12, 6, 2, 'Design database schema',  '2025-05-23', 'Important', 'In Progress'),
    (13, 6, 3, 'Write test cases',        '2025-05-28', 'Low',       'Not Started'),
    (14, 7, 1, 'Set up CI pipeline',      '2025-05-15', 'Important', 'Completed');

-- User-Collab Links
-- User1: Can Edit (2), Guest: View Only (1)
INSERT INTO UserCollaborationLink (User_ID, CollabPage_ID, Permission_ID) VALUES
    (2, 1, 2),
    (3, 1, 1);

-- Invite Records
INSERT INTO Invite (Invite_ID, DestinationPage_ID, Sender, Recipient, Sent) VALUES
    (1, 1, 1, 'Username', TRUE),
    (2, 1, 1, 'Email',    TRUE);


-- =========================================================
-- MIGRATION ONLY (skip if importing fresh)
-- =========================================================
-- ALTER TABLE Users MODIFY COLUMN Password VARCHAR(255) NOT NULL;
-- ALTER TABLE Users ADD UNIQUE (Email), ADD UNIQUE (Username);
--
-- ALTER TABLE TaskList ADD COLUMN CollabPage_ID INTEGER NULL;
-- ALTER TABLE TaskList ADD FOREIGN KEY (CollabPage_ID) REFERENCES CollabPage(CollabPage_ID);
--
-- ALTER TABLE Tasks MODIFY COLUMN Progress ENUM('Not Started','In Progress','Completed') NOT NULL DEFAULT 'Not Started';
-- ALTER TABLE Tasks DROP FOREIGN KEY <old_fk_name>;
-- ALTER TABLE Tasks ADD FOREIGN KEY (AssignedTaskList) REFERENCES TaskList(TaskList_ID) ON DELETE CASCADE;
--
-- ALTER TABLE Invite DROP FOREIGN KEY <old_fk_on_sender>;
-- ALTER TABLE Invite ADD FOREIGN KEY (Sender) REFERENCES Users(User_ID);
-- =========================================================
