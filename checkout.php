<?php 
session_start();
include 'config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$booking_id = intval($_GET['id']);

// Fetch booking data to calculate sums
$stmt = $conn->prepare("
    SELECT b.*, r.room_number, r.room_type, r.price_per_night 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.id = ? AND b.status = 'Pending'
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Redirect back home if the booking doesn't exist or is already processed
if (!$booking) {
    header("Location: index.php");
    exit();
}

$days = (int) (new DateTime($booking['check_in']))->diff(new DateTime($booking['check_out']))->days;
$days = $days > 0 ? $days : 1;
$total_charge = $days * $booking['price_per_night'];

// Process simulated payment success capture
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    $update_stmt = $conn->prepare("UPDATE bookings SET status = 'Approved' WHERE id = ?");
    $update_stmt->bind_param("i", $booking_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        header("Location: receipt.php?id=" . $booking_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <!-- Loading Animation Screen Overlay -->
    <div id="paymentLoader" class="fixed inset-0 bg-slate-900/80 z-50 hidden flex-col items-center justify-center text-white">
        <div class="w-12 h-12 border-4 border-t-blue-500 border-gray-600 rounded-full animate-spin mb-4"></div>
        <p class="text-sm font-semibold tracking-wide">Processing Secure Transaction...</p>
        <p class="text-xs text-gray-400 mt-1">Please do not refresh or close this browser window.</p>
    </div>

    <div>
        <?php include 'config/navbar.php'; ?>

        <main class="max-w-4xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-5 gap-8">
            
            <!-- Payment Form Panel -->
            <div class="md:col-span-3 bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Payment Details</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Simulated sandbox gateway environment</p>
                </div>

                <form id="checkoutForm" method="POST" onsubmit="handlePaymentSubmit(event)" class="space-y-4">
                    <input type="hidden" name="process_payment" value="1">

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase">Cardholder Name</label>
                        <input type="text" required placeholder="John Doe" class="w-full mt-1 p-2.5 border text-sm rounded focus:outline-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase">Card Number</label>
                        <div class="relative mt-1">
                            <input type="text" required maxlength="19" placeholder="4111 2222 3333 4444" oninput="formatCardNumber(this)" class="w-full p-2.5 pr-10 border text-sm rounded focus:outline-blue-500">
                            <i class="fa-solid fa-credit-card absolute right-3 top-3.5 text-gray-450 text-sm"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Expiry Date</label>
                            <input type="text" required maxlength="5" placeholder="MM/YY" oninput="formatExpiry(this)" class="w-full mt-1 p-2.5 border text-sm rounded focus:outline-blue-500 text-center">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Security Code (CVV)</label>
                            <input type="password" required maxlength="3" placeholder="123" class="w-full mt-1 p-2.5 border text-sm rounded focus:outline-blue-500 text-center">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded text-sm transition mt-4 shadow-xs flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-lock mr-2"></i> Pay ₹<?php echo number_format($total_charge, 2); ?> securely
                    </button>
                </form>
            </div>

            <!-- Booking Order Summary Column -->
            <div class="md:col-span-2 space-y-4">
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b pb-2">Stay Summary</h3>
                    
                    <div class="text-sm">
                        <p class="font-bold text-blue-600 text-base"><?php echo htmlspecialchars($booking['room_number']); ?></p>
                        <p class="text-xs text-gray-400 font-medium"><?php echo htmlspecialchars($booking['room_type']); ?> Accommodation</p>
                    </div>

                    <div class="text-xs text-gray-600 space-y-1.5 pt-2 border-t">
                        <p><span class="font-semibold text-gray-800">Check-In:</span> <?php echo date('d M Y', strtotime($booking['check_in'])); ?></p>
                        <p><span class="font-semibold text-gray-800">Check-Out:</span> <?php echo date('d M Y', strtotime($booking['check_out'])); ?></p>
                        <p><span class="font-semibold text-gray-800">Duration:</span> <?php echo $days; ?> Night(s)</p>
                    </div>

                    <div class="flex justify-between items-center text-sm pt-4 border-t font-bold">
                        <span class="text-gray-500">Total Amount:</span>
                        <span class="text-emerald-600 text-lg">₹<?php echo number_format($total_charge, 2); ?></span>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Payment Processing Client Actions Scripts -->
    <script>
        // Formats card input blocks dynamically into groups of four numbers
        function formatCardNumber(input) {
            let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let matches = value.match(/\d{4,16}/g);
            let match = matches && matches[0] || '';
            let parts = [];

            for (let i=0, len=match.length; i<len; i+=4) {
                parts.push(match.substring(i, i+4));
            }

            if (parts.length > 0) {
                input.value = parts.join(' ');
            } else {
                input.value = value;
            }
        }

        // Formats input automatically with a forward slash for expiry dates
        function formatExpiry(input) {
            let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            if (value.length >= 2) {
                input.value = value.substring(0, 2) + '/' + value.substring(2, 4);
            } else {
                input.value = value;
            }
        }

        // Intercepts form post to run a visual countdown loader simulation
        function handlePaymentSubmit(event) {
            event.preventDefault();
            
            const loader = document.getElementById('paymentLoader');
            const form = document.getElementById('checkoutForm');
            
            // Render spinner UI
            loader.classList.remove('hidden');
            loader.classList.add('flex');
            
            // Wait 2.5 seconds before passing transaction values to database
            setTimeout(() => {
                form.submit();
            }, 2500);
        }
    </script>

    <?php include 'config/footer.php'; ?>
</body>
</html>