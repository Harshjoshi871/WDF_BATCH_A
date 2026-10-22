<?php
/**
 * Practical 8: Database Configuration & Connection (MySQLi & PDO)
 * StudentHub Portal - CHARUSAT ITUE203
 */

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "studenthub";
$port = 3306;

// 1. MySQLi Object-Oriented Connection (Used in Practicals 9, 11, 12, 13)
$conn = @new mysqli($host, $db_user, $db_pass, $db_name, $port);

if ($conn->connect_error) {
    // Graceful error display without exposing credentials
    die("<div style='font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:8px;max-width:600px;margin:40px auto;border:1px solid #f87171;'>
            <h3 style='margin-top:0;'>Database Connection Error (MySQLi)</h3>
            <p>Could not connect to database '<strong>$db_name</strong>'.</p>
            <p><small>Details: " . htmlspecialchars($conn->connect_error) . "</small></p>
            <p><em>Please make sure Apache and MySQL are running in XAMPP and the database is imported.</em></p>
         </div>");
}

$conn->set_charset("utf8mb4");

// 2. PDO Connection (Used for prepared statements / modern APIs)
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
    // If PDO fails, keep $pdo null or log
    $pdo = null;
    error_log("PDO Connection failed: " . $e->getMessage());
}
?>
