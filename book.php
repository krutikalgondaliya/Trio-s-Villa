<?php
include 'db.php';

if (!isset($_GET['room_id'])) {
    die("Invalid Access: No room selected.");
}

$room_id = intval($_GET['room_id']);
$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    die("Room not found.");
}

// Handle the Form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guest_name = $_POST['guest_name'];
    $guest_email = $_POST['guest_email'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    $book_stmt = $conn->prepare("INSERT INTO bookings (room_id, guest_name, guest_email, check_in, check_out) VALUES (?, ?, ?, ?, ?)");
    $book_stmt->bind_param("issss", $room_id, $guest_name, $guest_email, $check_in, $check_out);
    
    if ($book_stmt->execute()) {
        $message = "<div class='bg-green-100 text-green-700 p-4 rounded mb-4'>Booking requested successfully!</div>";
    } else {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>Error processing booking.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Book Your Stay</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 py-12">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Book <?php echo $room['room_number']; ?></h2>
        <p class="text-blue-600 font-semibold mb-6"><?php echo $room['room_type']; ?> - ₹<?php echo number_format($room['price_per_night'], 2); ?>/night</p>
        
        <?php echo $message; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="guest_name" required class="w-full mt-1 p-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="guest_email" required class="w-full mt-1 p-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Check-in Date</label>
                <input type="date" name="check_in" required class="w-full mt-1 p-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Check-out Date</label>
                <input type="date" name="check_out" required class="w-full mt-1 p-2 border rounded">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Confirm Booking</button>
        </form>
    </div>
</body>
</html>