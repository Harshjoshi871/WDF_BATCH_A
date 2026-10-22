<?php
/**
 * Practical 15: Centralized Database Connection
 * StudentHub Full-Stack Portal
 */

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "studenthub";
$port = 3306;

// MySQLi Connection
$conn = @new mysqli($host, $db_user, $db_pass, $db_name, $port);

if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;padding:24px;background:#fee2e2;color:#991b1b;border-radius:8px;max-width:650px;margin:50px auto;border:1px solid #f87171;'>
            <h2 style='margin-top:0;'>Database Connection Error</h2>
            <p>Could not connect to database '<strong>$db_name</strong>'.</p>
            <p>Error details: " . htmlspecialchars($conn->connect_error) . "</p>
            <p>Please ensure MySQL is running in XAMPP and <code>studenthub_master.sql</code> is imported.</p>
         </div>");
}

$conn->set_charset("utf8mb4");

// PDO Connection
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    $pdo = null;
    error_log("PDO Connection Error: " . $e->getMessage());
}
?>
