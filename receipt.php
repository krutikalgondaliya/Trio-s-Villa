<?php
session_start();
include 'config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$booking_id = intval($_GET['id']);

// Pull booking specifics coupled with room financial properties
$stmt = $conn->prepare("
    SELECT b.*, r.room_number, r.room_type, r.price_per_night 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    header("Location: index.php");
    exit();
}

// Compute length of stay totals
$days = (int) (new DateTime($booking['check_in']))->diff(new DateTime($booking['check_out']))->days;
$days = $days > 0 ? $days : 1;
$total_charge = $days * $booking['price_per_night'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Invoice Summary #<?php echo $booking['id']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <div>
        <?php include 'config/navbar.php'; ?>

        <main class="max-w-2xl mx-auto px-6 my-12 print:my-0 print:px-0">
            <!-- Functional Navigation Helpers -->
            <div class="flex justify-between items-center mb-6 print:hidden">
                <a href="index.php" class="text-blue-600 hover:text-blue-700 font-medium inline-flex items-center text-sm transition">
                    <i class="fa-solid fa-house mr-2"></i> Return Home
                </a>
                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-1.5 rounded text-xs shadow-sm transition inline-flex items-center cursor-pointer">
                    <i class="fa-solid fa-print mr-2"></i> Print Invoice
                </button>
            </div>

            <!-- Printable Receipt Slip Structure -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-8 print:shadow-none print:border-none print:p-0">
                <div class="flex justify-between items-start border-b pb-6">
                    <div>
                        <h1 class="text-2xl font-black tracking-wide text-gray-900 flex items-center"><i class="fa-solid fa-hotel text-blue-600 mr-2"></i> HOTEL RESORT</h1>
                        <p class="text-xs text-gray-450 mt-1">123 Resort Boulevard, Beachside Luxury</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs bg-amber-50 text-amber-700 px-2.5 py-0.5 rounded font-bold uppercase tracking-wider border border-amber-100"><?php echo $booking['status']; ?> Acknowledgement</span>
                        <p class="text-xs font-mono text-gray-400 mt-2">Invoice: #INV-<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                </div>

                <!-- Profile summary rows -->
                <div class="grid grid-cols-2 gap-6 py-6 border-b text-sm">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Registered Guest Details</h3>
                        <p class="font-bold text-gray-900"><?php echo htmlspecialchars($booking['guest_name']); ?></p>
                        <p class="text-xs text-gray-500 mt-0.5"><?php echo htmlspecialchars($booking['guest_email']); ?></p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Reservation Framework</h3>
                        <p class="text-gray-700"><span class="font-semibold text-gray-900">Check-In:</span> <?php echo date('d M Y', strtotime($booking['check_in'])); ?></p>
                        <p class="text-gray-700 mt-0.5"><span class="font-semibold text-gray-900">Check-Out:</span> <?php echo date('d M Y', strtotime($booking['check_out'])); ?></p>
                    </div>
                </div>

                <!-- Ledger list summary layout -->
                <div class="py-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Itemized Billing</h3>
                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 font-semibold border-b text-xs uppercase">
                                    <th class="p-3">Description</th>
                                    <th class="p-3 text-center">Nights</th>
                                    <th class="p-3 text-right">Rate</th>
                                    <th class="p-3 text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr>
                                    <td class="p-3">
                                        <div class="font-bold text-gray-900"><?php echo htmlspecialchars($booking['room_number']); ?></div>
                                        <div class="text-[10px] text-gray-400 uppercase font-medium mt-0.5"><?php echo htmlspecialchars($booking['room_type']); ?> Tier Accommodation</div>
                                    </td>
                                    <td class="p-3 text-center font-medium text-gray-700"><?php echo $days; ?></td>
                                    <td class="p-3 text-right text-gray-600">₹<?php echo number_format($booking['price_per_night'], 2); ?></td>
                                    <td class="p-3 text-right font-bold text-gray-900">₹<?php echo number_format($total_charge, 2); ?></td>
                                </tr>
                                <tr class="bg-gray-50 font-bold text-base">
                                    <td colspan="3" class="p-3 text-right uppercase text-xs font-semibold tracking-wider text-gray-400">Total Charged Balance:</td>
                                    <td class="p-3 text-right text-emerald-600 font-black">₹<?php echo number_format($total_charge, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer notice segment -->
                <div class="border-t pt-4 text-center">
                    <p class="text-[11px] text-gray-400 leading-relaxed">Please retain this reference confirmation. If your reservation state shows "Pending," an email receipt update will be dispatched as soon as backend hotel management confirms date validations.</p>
                </div>
            </div>
        </main>
    </div>

    <?php include 'config/footer.php'; ?>
</body>
</html>