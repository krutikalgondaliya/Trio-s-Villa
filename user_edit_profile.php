<?php
session_start();
include 'config/db.php';

// Route away if user is unauthenticated
if (!isset($_SESSION['guest_logged_in']) || $_SESSION['guest_logged_in'] !== true) {
    header("Location: user_login.php");
    exit();
}

$guest_id = $_SESSION['guest_id'];
$message = "";

// Fetch current user details
$user_stmt = $conn->prepare("SELECT * FROM guest_users WHERE id = ?");
$user_stmt->bind_param("i", $guest_id);
$user_stmt->execute();
$user_info = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $profile_pic = $user['profile_pic'] ?? 'default_avatar.png'; // Default to current pic
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($fullname) && !empty($email)) {
        
        // 1. Handle Profile Image Upload (If Selected)
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['avatar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                if ($_FILES['avatar']['size'] <= 2 * 1024 * 1024) { // 2MB Limit
                    $upload_dir = __DIR__ . "/uploads/";
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    if (!empty($user_info['profile_pic']) && file_exists($upload_dir . $user_info['profile_pic'])) {
                        unlink($upload_dir . $user_info['profile_pic']);
                    }

                    $new_filename = "user_" . $guest_id . "_" . time() . "." . $ext;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $new_filename)) {
                        $profile_pic = $new_filename;
                    }
                } else {
                    $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Avatar must be less than 2MB.</div>";
                }
            } else {
                $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Invalid file extension.</div>";
            }
        }

        // 2. Handle Password Change Logic (If Fields Formed)
        $password_updated = false;
        $hashed_password = $user_info['password']; 

        if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
            if (password_verify($current_password, $user_info['password'])) {
                if (!empty($new_password) && strlen($new_password) >= 6) {
                    if ($new_password === $confirm_password) {
                        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                        $password_updated = true;
                    } else {
                        $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>New passwords do not match.</div>";
                    }
                } else {
                    $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>New password must be at least 6 characters long.</div>";
                }
            } else {
                $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Incorrect current password.</div>";
            }
        }

        // 3. Update Database Records
        if (empty($message)) {
            $update_stmt = $conn->prepare("UPDATE guest_users SET fullname = ?, email = ?, profile_pic = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("ssssi", $fullname, $email, $profile_pic, $hashed_password, $guest_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['guest_name'] = $fullname;
                $_SESSION['guest_email'] = $email;
                
                $success_text = "Profile updated successfully!";
                if ($password_updated) {
                    $success_text .= " Password has also been updated.";
                }
                
                $message = "<div class='bg-green-50 text-green-700 p-3 rounded text-sm mb-4 border border-green-100'>$success_text</div>";
                
                // Refresh local values
                $user_info['fullname'] = $fullname;
                $user_info['email'] = $email;
                $user_info['profile_pic'] = $profile_pic;
                $user_info['password'] = $hashed_password;
            } else {
                $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Error saving account details.</div>";
            }
            $update_stmt->close();
        }
    } else {
        $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>All primary profile fields are required.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <div>
        <?php include 'config/navbar.php'; ?>

        <main class="max-w-md w-full mx-auto px-6 my-12">
            <div class="mb-4">
                <a href="user_profile.php" class="text-blue-600 hover:underline inline-flex items-center text-xs font-semibold">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Back to Profile Dashboard
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 bg-blue-600 text-center text-white">
                    <h2 class="text-lg font-bold">Account Settings</h2>
                    <p class="text-xs text-blue-100 mt-0.5">Modify profiles and access credentials</p>
                </div>

                <div class="p-6">
                    <?php echo $message; ?>

                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <!-- Profile Photo Picker -->
                        <div class="flex flex-col items-center mb-4">
                            <div class="w-20 h-20 mb-2">
                                <?php if(!empty($user_info['profile_pic']) && file_exists("uploads/" . $user_info['profile_pic'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($user_info['profile_pic']); ?>" class="w-20 h-20 rounded-full object-cover border">
                                <?php else: ?>
                                    <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold border">
                                        <?php echo strtoupper(substr($user_info['fullname'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Replace Avatar Image</label>
                            <input type="file" name="avatar" accept="image/*" class="text-xs text-gray-500 file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- Basic Account Fields -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Full Name</label>
                            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user_info['fullname']); ?>" required class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                        </div>
                        
                        <!-- Change Password Section -->
                        <div class="border-t pt-4 mt-6">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Change Password (Optional)</h3>
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-600 font-medium">Current Password</label>
                                    <input type="password" name="current_password" placeholder="Enter old password" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 font-medium">New Password</label>
                                    <input type="password" name="new_password" placeholder="At least 6 characters" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 font-medium">Confirm New Password</label>
                                    <input type="password" name="confirm_password" placeholder="Repeat new password" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_profile" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded text-sm transition shadow-xs mt-4">
                            Save Account Updates
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <?php include 'config/footer.php'; ?>
</body>
</html>