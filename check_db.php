<?php
// Enable full error display for this diagnostic test
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Railway MySQL Connection Diagnostic</h2>";

// 1. Verify extension
if (!extension_loaded('mysqli')) {
    die("<p style='color:red;'><b>ERROR:</b> MySQLi extension is not loaded.</p>");
}
echo "<p style='color:green;'><b>PASS:</b> MySQLi extension is active.</p>";

// 2. Include database configuration
$db_file = __DIR__ . '/config/db.php';
if (!file_exists($db_file)) {
    die("<p style='color:red;'><b>ERROR:</b> Could not find config/db.php at: " . htmlspecialchars($db_file) . "</p>");
}
require_once $db_file;

// 3. Verify connection object
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("<p style='color:red;'><b>ERROR:</b> \$conn is not an instance of mysqli. Check config/db.php.</p>");
}

// 4. Test a live query
$result = $conn->query("SELECT DATABASE() AS current_db, VERSION() AS db_version");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p style='color:green;'><b>PASS:</b> Successfully connected to MySQL!</p>";
    echo "<ul>";
    echo "<li><b>Database Name:</b> " . htmlspecialchars($row['current_db'] ?? 'None selected') . "</li>";
    echo "<li><b>MySQL Version:</b> " . htmlspecialchars($row['db_version']) . "</li>";
    echo "<li><b>Host Info:</b> " . htmlspecialchars($conn->host_info) . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red;'><b>ERROR:</b> Connected, but query failed: " . htmlspecialchars($conn->error) . "</p>";
}
?>
