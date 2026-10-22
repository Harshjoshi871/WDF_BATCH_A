<?php
/**
 * Practical 8: Database Connection Test Page
 */
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 8 - Database Connection Test</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; padding: 40px 20px; }
        .card { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 32px; }
        h1 { color: #4338ca; margin-top: 0; font-size: 24px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; }
        .badge-success { display: inline-block; background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 9999px; font-weight: bold; font-size: 14px; margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
        .info-row strong { color: #4b5563; }
        .table-list { margin-top: 20px; }
        .table-list li { margin: 6px 0; color: #374151; }
        .btn { display: inline-block; margin-top: 24px; background: #4f46e5; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; transition: background 0.2s; }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="card">
        <h1>StudentHub - Database Connection Status</h1>
        <div class="badge-success">✓ Connected Successfully</div>

        <div class="info-row">
            <strong>Host:</strong> <span><?= htmlspecialchars($host) ?></span>
        </div>
        <div class="info-row">
            <strong>Database:</strong> <span><?= htmlspecialchars($db_name) ?></span>
        </div>
        <div class="info-row">
            <strong>Server Version:</strong> <span><?= htmlspecialchars($conn->server_info) ?></span>
        </div>
        <div class="info-row">
            <strong>Charset:</strong> <span><?= htmlspecialchars($conn->character_set_name()) ?></span>
        </div>
        <div class="info-row">
            <strong>PDO Status:</strong> <span><?= $pdo ? "Active & Ready" : "Inactive" ?></span>
        </div>

        <h3 style="margin-top: 24px; color:#374151;">Tables Detected in Database:</h3>
        <ul class="table-list">
            <?php
            $result = $conn->query("SHOW TABLES");
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_array()) {
                    echo "<li>📁 <strong>" . htmlspecialchars($row[0]) . "</strong></li>";
                }
            } else {
                echo "<li>No tables found. Please run <code>studenthub_master.sql</code></li>";
            }
            ?>
        </ul>

        <a href="../Practical 9/register.php" class="btn">Proceed to Practical 9 &rarr;</a>
    </div>
</body>
</html>
