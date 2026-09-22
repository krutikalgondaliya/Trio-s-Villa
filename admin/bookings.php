<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';

$message = "";

// Handle status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'] == 'approve' ? 'Approved' : 'Cancelled';

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $id);
    if ($stmt->execute()) {
        $message = "<div class='bg-green-50 text-green-700 p-3 rounded text-sm mb-4 border border-green-100'>Booking state updated to $action!</div>";
    }
    $stmt->close();
}

$search_id = isset($_GET['search_id']) ? intval($_GET['search_id']) : 0;

$bookings_result = $conn->query("
    SELECT b.*, r.room_number, r.room_type 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    ORDER BY b.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom Thin Scrollbar for sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(51, 65, 85, 0.8);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #3b82f6;
        }

        /* Active Menu Link Style */
        .sidebar-link-active { 
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.15) 0%, rgba(30, 41, 59, 0.5) 100%);
            border-left: 3px solid #3b82f6;
            color: #ffffff !important;
            font-weight: 700;
        }
        .sidebar-link-active i {
            color: #3b82f6 !important;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans min-h-screen flex">

    <!-- Left Sidebar Dynamic Container -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col fixed h-full z-20 border-r border-slate-800 shadow-xl">
        
        <!-- Branding Header -->
        <div class="p-5 flex items-center space-x-3 border-b border-slate-800 shrink-0">
            <div class="w-10 h-10 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center border border-blue-500/30">
                <i class="fa-solid fa-hotel text-xl"></i>
            </div>
            <div>
                <span class="text-base font-extrabold tracking-wider text-white uppercase block leading-tight">TRIO'S VILLA</span>
                <span class="text-[10px] font-bold text-slate-400 tracking-widest uppercase">Admin Panel</span>
            </div>
        </div>
        
        <!-- Navigation Links Container (Scrollable) -->
        <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-1 custom-scrollbar">
            
            <!-- SECTION: MAIN -->
            <p class="px-3 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Main</p>
            
            <a href="index.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-house w-5 text-center text-slate-400 group-hover:text-blue-400 transition"></i> 
                <span>Dashboard</span>
            </a>

            <!-- SECTION: ROOM MANAGEMENT -->
            <p class="px-3 pt-4 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Room Operations</p>

            <a href="room_types.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-layer-group w-5 text-center text-slate-400 group-hover:text-blue-400 transition"></i> 
                <span>Room Types</span>
            </a>

            <a href="rooms.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-bed w-5 text-center text-slate-400 group-hover:text-blue-400 transition"></i> 
                <span>Rooms Inventory</span>
            </a>

            

            <!-- SECTION: BOOKINGS & GUESTS -->
            <p class="px-3 pt-4 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Reservations & Guests</p>

            <a href="calendar.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-calendar-days w-5 text-center text-slate-400 group-hover:text-blue-400 transition"></i> 
                <span>Reservation Calendar</span>
            </a>

            <a href="bookings.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg transition text-xs font-semibold sidebar-link-active group">
                <i class="fa-solid fa-calendar-check w-5 text-center transition"></i> 
                <span>Bookings</span>
            </a>

            <a href="customers.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-users w-5 text-center text-slate-400 group-hover:text-blue-400 transition"></i> 
                <span>Customers</span>
            </a>

            <!-- SECTION: FINANCE & MARKETING -->
            <p class="px-3 pt-4 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Finance & Marketing</p>

            <a href="payments.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-credit-card w-5 text-center text-slate-400 group-hover:text-blue-400 transition"></i> 
                <span>Payments</span>
            </a>

            <a href="coupons.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-ticket w-5 text-center text-slate-400 group-hover:text-amber-400 transition"></i> 
                <span>Promo Codes</span>
            </a>

            <a href="reports.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-chart-line w-5 text-center text-slate-400 group-hover:text-indigo-400 transition"></i> 
                <span>Reports & Analytics</span>
            </a>

            <!-- SECTION: CONTENT & COMMUNICATIONS -->
            <p class="px-3 pt-4 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Content & Support</p>

            <a href="reviews.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-star w-5 text-center text-slate-400 group-hover:text-amber-400 transition"></i> 
                <span>Guest Reviews</span>
            </a>

            <a href="gallery.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-image w-5 text-center text-slate-400 group-hover:text-blue-400 transition"></i> 
                <span>Gallery</span>
            </a>

            <a href="messages.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-envelope w-5 text-center text-slate-400 group-hover:text-blue-400 transition"></i> 
                <span>Messages</span>
            </a>

        </nav>

        <!-- Logout / Admin Profile Footer -->
        <div class="p-3 border-t border-slate-800 bg-slate-950/40 shrink-0">
            <a href="logout.php" class="flex items-center space-x-3 px-3.5 py-2 rounded-lg text-xs font-bold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition">
                <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> 
                <span>Sign Out</span>
            </a>
        </div>

    </aside>

    <!-- Right Side Content Container -->
    <div class="flex-1 flex flex-col pl-64 justify-between min-h-screen">
        <div>
            <header class="bg-white border-b px-8 py-4 flex justify-between items-center shadow-sm relative">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Reservation Registry</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Control visitor booking pipelines</p>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="relative hidden md:block">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        <input type="text" id="bookingSearch" onkeyup="filterBookings()" placeholder="Search guest name..." class="pl-9 pr-4 py-2 border rounded-full text-xs focus:outline-blue-500 w-64">
                    </div>

                    <div class="relative">
                        <button onclick="toggleUserDropdown(event)" class="flex items-center space-x-3 focus:outline-none hover:bg-gray-50 p-2 rounded-lg transition duration-250">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                A
                            </div>
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-gray-800 leading-tight">admin</p>
                                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Administrator <i class="fa-solid fa-chevron-down ml-1 text-[8px]"></i></p>
                            </div>
                        </button>

                        <div id="adminDropdownMenu" class="absolute right-0 mt-2 w-48 bg-white border rounded-xl shadow-lg py-2 hidden z-30 transition duration-200 origin-top-right">
                            <div class="px-4 py-2 border-b sm:hidden">
                                <p class="text-xs font-bold text-gray-800">admin</p>
                                <p class="text-[9px] text-gray-400 font-semibold uppercase">Administrator</p>
                            </div>
                            <a href="index.php" class="flex items-center space-x-2 px-4 py-2 text-xs text-gray-750 hover:bg-gray-50 transition">
                                <i class="fa-solid fa-house text-gray-400 w-4"></i> <span>Dashboard Home</span>
                            </a>
                            <a href="reports.php" class="flex items-center space-x-2 px-4 py-2 text-xs text-gray-750 hover:bg-gray-50 transition">
                                <i class="fa-solid fa-chart-line text-gray-400 w-4"></i> <span>View Reports</span>
                            </a>
                            <hr class="my-1 border-gray-100">
                            <a href="logout.php" class="flex items-center space-x-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-bold transition">
                                <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> <span>Logout System</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-8">
                <?php echo $message; ?>
                <div class="bg-white rounded-xl border shadow-xs overflow-hidden">
                    <table class="w-full text-left border-collapse" id="bookingTable">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase border-b">
                                <th class="p-4">Guest</th>
                                <th class="p-4">Room Details</th>
                                <th class="p-4">Stay Timelines</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            <?php if ($bookings_result->num_rows > 0): ?>
                                <?php while($b = $bookings_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50/40 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900"><?php echo htmlspecialchars($b['guest_name']); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo htmlspecialchars($b['guest_email']); ?></div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-blue-600">Room <?php echo htmlspecialchars($b['room_number']); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo htmlspecialchars($b['room_type']); ?></div>
                                    </td>
                                    <td class="p-4 text-xs font-medium text-gray-600">
                                        <?php echo date('d M', strtotime($b['check_in'])) . ' - ' . date('d M Y', strtotime($b['check_out'])); ?>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded <?php echo ($b['status'] == 'Approved') ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100'; ?>">
                                            <?php echo $b['status']; ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center space-x-2">
                                        <?php if($b['status'] !== 'Approved'): ?>
                                            <a href="bookings.php?action=approve&id=<?php echo $b['id']; ?>" class="text-emerald-600 hover:underline text-xs font-bold">Approve</a>
                                        <?php endif; ?>
                                        <?php if($b['status'] !== 'Cancelled'): ?>
                                            <a href="bookings.php?action=cancel&id=<?php echo $b['id']; ?>" class="text-red-600 hover:underline text-xs font-bold">Cancel</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-400">No reservations registered.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>

        <footer class="bg-white border-t py-4 px-8 text-center text-xs font-medium text-gray-500">
            <span class="font-bold text-gray-800">Trio's Villa</span> &copy; 2026 All Rights Reserved.
        </footer>
    </div>

    <script>
        function toggleUserDropdown(event) {
            event.stopPropagation();
            document.getElementById('adminDropdownMenu').classList.toggle('hidden');
        }

        function filterBookings() {
            const filter = document.getElementById('bookingSearch').value.toUpperCase();
            const tr = document.getElementById('bookingTable').getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                tr[i].style.display = (tr[i].getElementsByTagName('td')[0].innerText.toUpperCase().indexOf(filter) > -1) ? "" : "none";
            }
        }

        window.addEventListener('click', () => {
            const dropdown = document.getElementById('adminDropdownMenu');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>