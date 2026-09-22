<?php
// config/header.php
include_once 'db.php'; 

// Fetch the path from the database
$query = $conn->query("SELECT setting_value FROM site_settings WHERE setting_name = 'favicon'");
$row = $query->fetch_assoc();
$favicon = $row['setting_value'] ?? 'assets/favicon.ico';
?>
<link rel="icon" type="image/x-icon" href="<?php echo $favicon; ?>?v=<?php echo time(); ?>">