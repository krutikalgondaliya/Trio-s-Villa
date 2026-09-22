<?php
include 'db.php';

// Handle Booking Status Approvals
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $id = intval($_GET['booking_id']);
    $status = ($_GET['action'] == 'approve') ? 'Approved' : 'Cancelled';
    
    $update_stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $status, $id);
    $update_stmt->execute();
    header("Location: admin.php");
    exit();
}

// Fetch current bookings
$bookings = $conn->query("SELECT b.*, r.room_number, r.room_type FROM bookings b JOIN rooms r ON b.room_id = r.id ORDER BY b.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Admin Management Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Hotel Management Administration</h1>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-700">Incoming Room Reservations</h2>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                        <th class="p-4">Guest Name</th>
                        <th class="p-4">Room No. / Type</th>
                        <th class="p-4">Check In</th>
                        <th class="p-4">Check Out</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm">
                    <?php while($row = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td class="p-4 font-medium"><?php echo htmlspecialchars($row['guest_name']); ?><br><span class="text-gray-400 text-xs"><?php echo htmlspecialchars($row['guest_email']); ?></span></td>
                        <td class="p-4"><?php echo $row['room_number']; ?> (<?php echo $row['room_type']; ?>)</td>
                        <td class="p-4"><?php echo $row['check_in']; ?></td>
                        <td class="p-4"><?php echo $row['check_out']; ?></td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs font-semibold <?php echo ($row['status']=='Approved') ? 'bg-green-100 text-green-700' : (($row['status']=='Cancelled') ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td class="p-4 space-x-2">
                            <?php if($row['status'] == 'Pending'): ?>
                                <a href="admin.php?action=approve&booking_id=<?php echo $row['id']; ?>" class="text-green-600 hover:underline">Approve</a>
                                <a href="admin.php?action=cancel&booking_id=<?php echo $row['id']; ?>" class="text-red-600 hover:underline">Cancel</a>
                            <?php else: ?>
                                <span class="text-gray-400 font-light">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>