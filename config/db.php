<?php
// Check for Railway's connection URL first (Private or Public)
$dbUrl = getenv('MYSQL_URL') ?: getenv('MYSQL_PRIVATE_URL') ?: getenv('DATABASE_URL');

if ($dbUrl) {
    $dbParts = parse_url($dbUrl);

    $host   = $dbParts['host'];
    $port   = isset($dbParts['port']) ? (int)$dbParts['port'] : 3306;
    $user   = $dbParts['user'];
    $pass   = isset($dbParts['pass']) ? $dbParts['pass'] : '';
    $dbname = ltrim($dbParts['path'], '/');
} else {
    // Fall back to local XAMPP / individual variables
    $host   = getenv('MYSQLHOST') ?: "localhost";
    $user   = getenv('MYSQLUSER') ?: "root";
    $pass   = getenv('MYSQLPASSWORD') ?: "";
    $dbname = getenv('MYSQLDATABASE') ?: "hotel_db1";
    $port   = (int)(getenv('MYSQLPORT') ?: 3306);
}

// Establish the connection
$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Fail: " . $conn->connect_error);
}

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
