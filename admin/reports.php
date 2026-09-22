<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';

// Compile reporting statistics metrics
$total_approved = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status='Approved'")->fetch_assoc()['count'] ?? 0;
$total_cancelled = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status='Cancelled'")->fetch_assoc()['count'] ?? 0;

$revenue_total = 0;
$rev_query = $conn->query("
    SELECT b.check_in, b.check_out, r.price_per_night 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.status = 'Approved'
");
while ($row = $rev_query->fetch_assoc()) {
    $days = (int) (new DateTime($row['check_in']))->diff(new DateTime($row['check_out']))->days;
    $days = $days > 0 ? $days : 1;
    $revenue_total += ($days * $row['price_per_night']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Terminal - Admin Panel</title>
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

            <a href="bookings.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-calendar-check w-5 text-center text-slate-400 group-hover:text-blue-400 transition"></i> 
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

            <a href="reports.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg transition text-xs font-semibold sidebar-link-active group">
                <i class="fa-solid fa-chart-line w-5 text-center transition"></i> 
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
                    <h1 class="text-2xl font-bold text-gray-800">Reports Terminal</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Hotel production metric indexes</p>
                </div>

                <!-- Upper Right Dropdown Component Container -->
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

                    <!-- Dropdown Element Options Overlay -->
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
            </header>

            <main class="p-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl border shadow-xs">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Gross Billings</span>
                    <h2 class="text-2xl font-black text-emerald-600 mt-1">₹<?php echo number_format($revenue_total, 2); ?></h2>
                </div>
                <div class="bg-white p-6 rounded-xl border shadow-xs">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Successful Stays</span>
                    <h2 class="text-2xl font-black text-blue-600 mt-1"><?php echo $total_approved; ?></h2>
                </div>
                <div class="bg-white p-6 rounded-xl border shadow-xs">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Cancelled Tickets</span>
                    <h2 class="text-2xl font-black text-red-600 mt-1"><?php echo $total_cancelled; ?></h2>
                </div>
            </main>
        </div>

        <footer class="bg-white border-t py-4 px-8 text-center text-xs font-medium text-gray-500">
            <span class="font-bold text-gray-800">Trio's Villa</span> &copy; 2026 All Rights Reserved.
        </footer>
    </div>

    <!-- Dropdown Control Logic Script -->
    <script>
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('adminDropdownMenu');
            dropdown.classList.toggle('hidden');
        }

        window.addEventListener('click', function() {
            const dropdown = document.getElementById('adminDropdownMenu');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>