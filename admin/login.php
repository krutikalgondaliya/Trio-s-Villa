<?php
session_start();

// Redirect to dashboard if already authenticated
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Default system fallback credentials (Change these to match your database preferences)
    $admin_user = "admin";
    $admin_pass_hash = password_hash("admin123", PASSWORD_BCRYPT); 

    if ($username === $admin_user && password_verify($password, $admin_pass_hash)) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid management login credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center font-sans p-4">

    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-100">
        <div class="p-6 bg-slate-800 text-center text-white border-b">
            <h2 class="text-xl font-bold uppercase tracking-wider">Hotel Admin Panel</h2>
            <p class="text-xs text-slate-400 mt-1">Management Portal Access Doorway</p>
        </div>
        
        <div class="p-6">
            <?php if(!empty($error)): ?>
                <p class="bg-red-50 text-red-700 p-3 rounded text-xs font-semibold mb-4 border border-red-100"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Username</label>
                    <input type="text" name="username" required placeholder="admin" class="w-full mt-1 p-2.5 border text-sm rounded focus:outline-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full mt-1 p-2.5 border text-sm rounded focus:outline-slate-800">
                </div>
                <button type="submit" name="login" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded text-sm transition tracking-wide mt-2 shadow">
                    Login to Dashboard
                </button>
            </form>
        </div>
    </div>

</body>
</html>