<?php
// PHP 8.1+ throws mysqli_sql_exception on connection failure by default.
// Wrapping in try/catch gives us a clean error message instead of a fatal stack trace.
try {
    $conn = new mysqli("localhost", "root", "", "tsm");
} catch (mysqli_sql_exception $e) {
    // Check if the database simply doesn't exist yet
    if (str_contains($e->getMessage(), "Unknown database")) {
        die(
            "<h2 style='font-family:monospace;color:red;padding:20px;'>
             Database not found.<br><br>
             <span style='font-size:0.8em;color:#ccc;'>
             Open <a href='http://localhost/phpmyadmin' style='color:lightblue;'>phpMyAdmin</a>,
             create a database named <b>tsm</b>, then import
             <b>Application/Database/TaskManager_Schema.sql</b>.
             </span></h2>"
        );
    }
    die("Database connection failed: " . $e->getMessage());
}
