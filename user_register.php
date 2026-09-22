<?php
session_start();
include 'config/db.php';

// Redirect to profile if already logged in
if (isset($_SESSION['guest_logged_in']) && $_SESSION['guest_logged_in'] === true) {
    header("Location: user_profile.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($full_name) && !empty($email) && !empty($password)) {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM guest_users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "An account with this email address already exists.";
        } else {
            // Hash password using BCRYPT to match profile update and login logic
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user into database using corrected column and variable names
            $stmt = $conn->prepare("INSERT INTO guest_users (fullname, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $full_name, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Error creating account. Please try again.";
            }
            $stmt->close();
        }
        $check_stmt->close();
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
    <title>Guest Registration - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <div>
        <?php include 'config/navbar.php'; ?>

        <main class="max-w-md w-full mx-auto px-6 my-16">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 bg-blue-600 text-center text-white">
                    <h2 class="text-xl font-bold">Create Guest Account</h2>
                    <p class="text-xs text-blue-100 mt-1">Sign up to reserve rooms and track your stay history</p>
                </div>

                <div class="p-6">
                    <?php if($error): ?><p class="bg-red-50 text-red-700 p-3 rounded text-xs font-semibold mb-4 border border-red-100"><?php echo $error; ?></p><?php endif; ?>
                    <?php if($success): ?><p class="bg-green-50 text-green-700 p-3 rounded text-xs font-semibold mb-4 border border-green-100"><?php echo $success; ?></p><?php endif; ?>

                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Full Name</label>
                            <input type="text" name="full_name" required placeholder="John Doe" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Email Address</label>
                            <input type="email" name="email" required placeholder="johndoe@example.com" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Password</label>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                        </div>
                        <button type="submit" name="register" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded text-sm transition">
                            Register Account
                        </button>
                    </form>

                    <div class="text-center mt-6 pt-4 border-t text-xs text-gray-500">
                        Already have an account? <a href="user_login.php" class="text-blue-600 hover:underline font-medium">Log in here</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include 'config/footer.php'; ?>
</body>
</html>