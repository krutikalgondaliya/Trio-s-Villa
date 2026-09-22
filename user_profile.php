<?php
session_start();
include 'config/db.php';

// Route away if user is unauthenticated
if (!isset($_SESSION['guest_logged_in']) || $_SESSION['guest_logged_in'] !== true) {
    header("Location: user_login.php");
    exit();
}

$guest_id = $_SESSION['guest_id'];

// Fetch latest user details from database directory
$user_stmt = $conn->prepare("SELECT * FROM guest_users WHERE id = ?");
$user_stmt->bind_param("i", $guest_id);
$user_stmt->execute();
$user_info = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Fetch specific booking history logs linked with this user's email matching layout criteria
$booking_stmt = $conn->prepare("
    SELECT b.*, r.room_number, r.room_type, r.price_per_night 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.guest_email = ? 
    ORDER BY b.id DESC
");
$booking_stmt->bind_param("s", $user_info['email']);
$booking_stmt->execute();
$bookings_result = $booking_stmt->get_result();
$booking_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <div>
        <?php include 'config/navbar.php'; ?>

        <main class="max-w-5xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Account Details Card Column Component -->
            <div class="md:col-span-1 bg-white p-6 rounded-xl border border-gray-100 shadow-xs h-fit flex flex-col items-center text-center">
                
                <!-- Profile Avatar Preview Frame logic -->
                <div class="w-24 h-24 mb-4">
                    <?php if(!empty($user_info['profile_pic']) && file_exists("uploads/" . $user_info['profile_pic'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($user_info['profile_pic']); ?>" class="w-24 h-24 rounded-full object-cover border-2 border-gray-100 shadow-xs">
                    <?php else: ?>
                        <div class="w-24 h-24 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-3xl font-bold border-2 border-blue-200">
                            <?php echo strtoupper(substr($user_info['fullname'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h2 class="text-xl font-bold text-gray-900 truncate max-w-full"><?php echo htmlspecialchars($user_info['fullname']); ?></h2>
                <p class="text-xs text-gray-400 font-medium mt-0.5 mb-6"><?php echo htmlspecialchars($user_info['email']); ?></p>
                
                <a href="user_edit_profile.php" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 rounded text-xs transition border flex items-center justify-center">
                    <i class="fa-solid fa-user-pen mr-2"></i> Edit Profile Details
                </a>
                <a href="user_logout.php" class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 rounded text-xs transition mt-2 border border-red-100 flex items-center justify-center">
                    <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Logout
                </a>
            </div>

            <!-- Booking History Log Columns component -->
            <div class="md:col-span-2 bg-white p-6 rounded-xl border border-gray-100 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">My Stay History</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Track and verify your reservation details</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase border-b">
                                <th class="p-4">Room</th>
                                <th class="p-4">Check-In / Out</th>
                                <th class="p-4">Total Price</th>
                                <th class="p-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm text-gray-700">
                            <?php if ($bookings_result->num_rows > 0): ?>
                                <?php while($b = $bookings_result->fetch_assoc()): 
                                    $days = (int) (new DateTime($b['check_in']))->diff(new DateTime($b['check_out']))->days;
                                    $days = $days > 0 ? $days : 1;
                                    $total_cost = $days * $b['price_per_night'];
                                ?>
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-blue-600"><?php echo htmlspecialchars($b['room_number']); ?></div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-wide font-medium"><?php echo htmlspecialchars($b['room_type']); ?></div>
                                    </td>
                                    <td class="p-4 text-xs font-medium text-gray-600">
                                        <div>In: <?php echo date('d M Y', strtotime($b['check_in'])); ?></div>
                                        <div class="text-gray-400 mt-0.5">Out: <?php echo date('d M Y', strtotime($b['check_out'])); ?></div>
                                    </td>
                                    <td class="p-4 font-bold text-gray-900">₹<?php echo number_format($total_cost, 2); ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded <?php echo ($b['status'] == 'Approved') ? 'bg-green-50 text-green-700 border border-green-100' : (($b['status'] == 'Cancelled') ? 'bg-red-50 text-red-700 border border-red-100' : 'bg-amber-50 text-amber-700 border border-amber-100'); ?>">
                                            <?php echo $b['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-400 text-xs">You have not submitted any room reservation logs yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <?php include 'config/footer.php'; ?>
</body>
</html>