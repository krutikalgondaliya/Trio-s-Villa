<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';

$email = isset($_GET['email']) ? $_GET['email'] : '';

// 1. Fetch History
$stmt = $conn->prepare("
    SELECT b.*, r.room_number, r.price_per_night 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.guest_email = ? 
    ORDER BY b.id DESC
");
$stmt->bind_param("s", $email);
$stmt->execute();
$history = $stmt->get_result();

// 2. Calculate Totals
$total_bookings = 0;
$total_spend = 0;
$bookings_data = [];
while ($row = $history->fetch_assoc()) {
    $bookings_data[] = $row;
    $total_bookings++;
    
    // Calculate nights for spend
    $days = (int) (new DateTime($row['check_in']))->diff(new DateTime($row['check_out']))->days;
    $days = $days > 0 ? $days : 1;
    if($row['status'] == 'Approved') {
        $total_spend += ($days * $row['price_per_night']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer History - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <a href="customers.php" class="text-xs text-gray-500 hover:text-gray-800 mb-4 inline-block"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Directory</a>
        
        <!-- Metrics Header -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <p class="text-[10px] uppercase font-bold text-gray-400">Total Bookings</p>
                <h3 class="text-xl font-black text-gray-800"><?php echo $total_bookings; ?></h3>
            </div>
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <p class="text-[10px] uppercase font-bold text-gray-400">Lifetime Revenue</p>
                <h3 class="text-xl font-black text-emerald-600">₹<?php echo number_format($total_spend, 2); ?></h3>
            </div>
        </div>

        <!-- History Table -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-base font-bold mb-4">Activity Log: <?php echo htmlspecialchars($email); ?></h2>
            <table class="w-full text-left">
                <tr class="text-xs text-gray-500 uppercase border-b">
                    <th class="p-3">Room</th>
                    <th class="p-3">Dates</th>
                    <th class="p-3">Status</th>
                </tr>
                <?php foreach($bookings_data as $b): ?>
                <tr class="border-b text-sm hover:bg-gray-50">
                    <td class="p-3 font-bold text-blue-600"><?php echo $b['room_number']; ?></td>
                    <td class="p-3 text-gray-600"><?php echo date('d M Y', strtotime($b['check_in'])); ?> - <?php echo date('d M Y', strtotime($b['check_out'])); ?></td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-[10px] font-bold <?php echo $b['status'] == 'Approved' ? 'bg-green-50 text-green-700 border' : 'bg-red-50 text-red-700 border'; ?>">
                            <?php echo $b['status']; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>