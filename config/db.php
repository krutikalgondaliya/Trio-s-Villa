<?php
// Report errors cleanly
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check for Railway environment variables
$dbUrl = getenv('MYSQL_URL') ?: getenv('MYSQL_PRIVATE_URL') ?: getenv('DATABASE_URL');

if ($dbUrl) {
    $parts  = parse_url($dbUrl);
    $host   = $parts['host'] ?? '127.0.0.1';
    $port   = isset($parts['port']) ? (int)$parts['port'] : 3306;
    $user   = $parts['user'] ?? 'root';
    $pass   = $parts['pass'] ?? '';
    $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
} else {
    // Read individual variables if set, otherwise fallback
    $host   = getenv('MYSQLHOST') ?: '127.0.0.1';
    $port   = (int)(getenv('MYSQLPORT') ?: 3306);
    $user   = getenv('MYSQLUSER') ?: 'root';
    $pass   = getenv('MYSQLPASSWORD') ?: '';
    $dbname = getenv('MYSQLDATABASE') ?: 'railway';
}

try {
    // Using IP/Host and explicit port avoids local Unix socket lookup
    $conn = new mysqli($host, $user, $pass, $dbname, $port);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

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
