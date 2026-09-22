<?php
// Ensure session is started if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database માંથી અપલોડ કરેલો dynamic આઇકન ફેચ કરવો
$site_icon = 'assets/favicon.ico';
if (isset($conn)) {
    $icon_query = $conn->query("SELECT setting_value FROM site_settings WHERE setting_name = 'favicon' LIMIT 1");
    if ($icon_query && $icon_query->num_rows > 0) {
        $db_val = trim($icon_query->fetch_assoc()['setting_value']);
        if (!empty($db_val) && file_exists(__DIR__ . '/../' . $db_val)) {
            $site_icon = $db_val;
        }
    }
}
?>
<header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-xs">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        
        <!-- Brand Logo & Dynamic Icon -->
        <a href="index.php" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl overflow-hidden bg-blue-50 border border-blue-100 flex items-center justify-center p-1 shadow-2xs">
                <img 
                    src="<?php echo htmlspecialchars($site_icon); ?>?v=<?php echo time(); ?>" 
                    alt="Trio's Villa Icon" 
                    class="w-full h-full object-contain"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fa-solid fa-hotel text-xl text-blue-600\'></i>';"
                >
            </div>
            <span class="text-xl font-bold tracking-wider text-gray-900 uppercase group-hover:text-blue-600 transition">
                TRIO'S VILLA
            </span>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold text-gray-600">
            <a href="index.php" class="hover:text-blue-600 transition">Home</a>
            <a href="index.php#rooms" class="hover:text-blue-600 transition">Rooms</a>
            <a href="gallery.php" class="hover:text-blue-600 transition">Gallery</a>
            <a href="reviews.php" class="hover:text-blue-600 transition">Reviews</a>
            <a href="contact.php" class="hover:text-blue-600 transition">Contact</a>
        </nav>

        <!-- Right Side: User Authentication Actions -->
        <div class="flex items-center space-x-4">
            <?php if (isset($_SESSION['guest_logged_in']) && $_SESSION['guest_logged_in'] === true): ?>
                <!-- Logged in State -->
                <a href="user_profile.php" class="flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-blue-600 transition">
                    <i class="fa-solid fa-circle-user text-lg text-blue-600"></i>
                    <span><?php echo htmlspecialchars($_SESSION['guest_name'] ?? 'My Account'); ?></span>
                </a>
                <a href="user_logout.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-4 py-2 rounded-lg transition">
                    Logout
                </a>
            <?php else: ?>
                <!-- Logged out State -->
                <a href="user_login.php" class="text-sm font-semibold text-gray-700 hover:text-blue-600 transition">
                    Log In
                </a>
                <a href="user_register.php" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition">
                    Sign Up
                </a>
            <?php endif; ?>
        </div>

    </div>
</header>