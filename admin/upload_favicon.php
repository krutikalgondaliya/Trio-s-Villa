<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

// Ensure settings table exists
$conn->query("CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_icon'])) {
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['favicon'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['ico', 'png', 'svg', 'jpg', 'jpeg'];

        if (in_array($ext, $allowed_exts)) {
            $target_dir = __DIR__ . '/../assets/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Cache-busting unique name so the browser refreshes the icon immediately
            $icon_name = "favicon." . $ext;
            $target_file = $target_dir . $icon_name;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $path_in_db = "assets/" . $icon_name;

                // Insert or update safely
                $stmt = $conn->prepare("
                    INSERT INTO site_settings (setting_name, setting_value) 
                    VALUES ('favicon', ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->bind_param("ss", $path_in_db, $path_in_db);
                $stmt->execute();
                $stmt->close();

                $message = "Favicon updated successfully and synced with database!";
            } else {
                $error = "Failed to upload file. Check folder write permissions.";
            }
        } else {
            $error = "Invalid file type. Allowed formats: .ico, .png, .svg";
        }
    } else {
        $error = "Please select a valid icon file.";
    }
}

// Fetch current favicon
$current_favicon = "assets/favicon.ico";
$get_fav = $conn->query("SELECT setting_value FROM site_settings WHERE setting_name = 'favicon' LIMIT 1");
if ($get_fav && $get_fav->num_rows > 0) {
    $current_favicon = $get_fav->fetch_assoc()['setting_value'];
}
?>

<div class="max-w-md bg-white p-6 border border-slate-200 rounded-2xl shadow-xs">
    <h3 class="text-sm font-bold text-slate-800 mb-4">Website Favicon Settings</h3>

    <?php if ($message): ?>
        <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="flex items-center gap-3 mb-5 p-3 bg-slate-50 rounded-xl border border-slate-100">
        <span class="text-xs text-slate-500 font-medium">Current Favicon:</span>
        <img src="../<?php echo htmlspecialchars($current_favicon); ?>?v=<?php echo time(); ?>" alt="Current Icon" class="w-6 h-6 object-contain" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏨</text></svg>';">
    </div>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Select New Favicon (.ico, .png):</label>
            <input type="file" name="favicon" required accept=".ico,.png,.svg" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
        </div>
        <button type="submit" name="update_icon" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs py-2.5 rounded-lg transition shadow-xs cursor-pointer">
            Upload New Favicon
        </button>
    </form>
</div>