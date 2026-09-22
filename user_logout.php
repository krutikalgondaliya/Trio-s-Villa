<?php
session_start();

// Unset explicit guest tracking parameters
unset($_SESSION['guest_logged_in']);
unset($_SESSION['guest_id']);
unset($_SESSION['guest_name']);
unset($_SESSION['guest_email']);

// Redirect cleanly back to the home page doorway
header("Location: index.php");
exit();
?>