<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "hotel_db1"; // Match this with your local phpMyAdmin database schema name

$conn = new mysqli($host, $user, $pass, $dbname);

// Terminate gracefully if standard system connections fail
if ($conn->connect_error) {
    die("Database Connection Fail: " . $conn->connect_error);
}

// Set global translation systems to decode Indian Rupee symbols (₹) and names accurately
$conn->set_charset("utf8mb4");

// Dynamic Favicon Fetch Function
function getSiteFavicon($conn) {
    $default = 'assets/favicon.ico';
    $res = $conn->query("SELECT setting_value FROM site_settings WHERE setting_name = 'favicon' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $val = trim($res->fetch_assoc()['setting_value']);
        if (!empty($val) && file_exists(__DIR__ . '/../' . $val)) {
            return $val;
        }
    }
    return $default;
}

?>