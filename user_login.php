<?php
session_start();
include 'config/db.php';

// Redirect to profile if already logged in
if (isset($_SESSION['guest_logged_in']) && $_SESSION['guest_logged_in'] === true) {
    header("Location: user_profile.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM guest_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['guest_logged_in'] = true;
            $_SESSION['guest_id'] = $user['id'];
            $_SESSION['guest_name'] = $user['full_name'];
            $_SESSION['guest_email'] = $user['email'];

            header("Location: user_profile.php");
            exit();
        } else {
            $error = "Invalid email or password credentials.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Login - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <div>
        <?php include 'config/navbar.php'; ?>

        <main class="max-w-md w-full mx-auto px-6 my-16">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 bg-blue-600 text-center text-white">
                    <h2 class="text-xl font-bold">Welcome Back</h2>
                    <p class="text-xs text-blue-100 mt-1">Log in to manage your bookings and account profile</p>
                </div>

                <div class="p-6">
                    <?php if($error): ?><p class="bg-red-50 text-red-700 p-3 rounded text-xs font-semibold mb-4 border border-red-100"><?php echo $error; ?></p><?php endif; ?>

                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Email Address</label>
                            <input type="email" name="email" required placeholder="yourname@example.com" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Password</label>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                        </div>
                        <button type="submit" name="login" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded text-sm transition">
                            Log In
                        </button>
                    </form>

                    <div class="text-center mt-6 pt-4 border-t text-xs text-gray-500">
                        Don't have an account? <a href="user_register.php" class="text-blue-600 hover:underline font-medium">Register here</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include 'config/footer.php'; ?>
</body>
</html>