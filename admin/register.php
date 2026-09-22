<?php
include '../config/db.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    
    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Username already exists.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded shadow-md max-w-sm w-full">
        <h2 class="text-xl font-bold mb-4 text-gray-850">Register Admin Account</h2>
        <?php if($error): ?><p class="text-red-600 text-sm mb-2"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" required class="w-full p-2 border rounded mt-1">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required class="w-full p-2 border rounded mt-1">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Register</button>
        </form>
        <p class="text-sm text-center mt-4 text-gray-500">Already registered? <a href="login.php" class="text-blue-600 hover:underline">Login here</a></p>
    </div>
</body>
</html>