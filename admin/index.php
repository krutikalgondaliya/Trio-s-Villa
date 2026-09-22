<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';

// 1. Compute basic tracking totals
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'] ?? 0;
$total_customers = $conn->query("SELECT COUNT(*) as count FROM guest_users")->fetch_assoc()['count'] ?? 0;
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'] ?? 0;

// 2. Fetch Chart Data: Monthly Revenue Trend for the current year
$current_year = date('Y');
$revenue_months = array_fill(1, 12, 0);

$revenue_query = $conn->query("
    SELECT b.check_in, b.check_out, r.price_per_night 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.status = 'Approved' AND YEAR(b.check_in) = '$current_year'
");

while ($row = $revenue_query->fetch_assoc()) {
    $month = (int)date('n', strtotime($row['check_in']));
    $nights = (int)(new DateTime($row['check_in']))->diff(new DateTime($row['check_out']))->days;
    $nights = $nights > 0 ? $nights : 1;
    $revenue_months[$month] += ($nights * $row['price_per_night']);
}

$chart_months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$chart_revenue = array_values($revenue_months);

// 3. Fetch Chart Data: Booking Distribution by Room Type
$room_type_labels = [];
$room_type_counts = [];
$type_query = $conn->query("
    SELECT r.room_type, COUNT(b.id) as count 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    GROUP BY r.room_type
");
while ($row = $type_query->fetch_assoc()) {
    $room_type_labels[] = $row['room_type'];
    $room_type_counts[] = (int)$row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            
            <a href="index.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg transition text-xs font-semibold sidebar-link-active group">
                <i class="fa-solid fa-house w-5 text-center transition"></i> 
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
                    <h1 class="text-2xl font-bold text-gray-800">Overview Dashboard</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Real-time status analysis</p>
                </div>
                
                <!-- Profile Action Menu Component with Interactive Dropdown Container -->
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

                    <!-- Dropdown Modal Overlay Menu Element -->
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

            <main class="p-8 space-y-8">
                <!-- Status Row Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl border shadow-xs flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase">Total Rooms</span>
                            <h2 class="text-2xl font-black text-slate-800 mt-1"><?php echo $total_rooms; ?></h2>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-lg"><i class="fa-solid fa-bed"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-xl border shadow-xs flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase">Registered Guests</span>
                            <h2 class="text-2xl font-black text-slate-800 mt-1"><?php echo $total_customers; ?></h2>
                        </div>
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-lg"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-xl border shadow-xs flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase">Bookings Recorded</span>
                            <h2 class="text-2xl font-black text-slate-800 mt-1"><?php echo $total_bookings; ?></h2>
                        </div>
                        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center text-lg"><i class="fa-solid fa-calendar-check"></i></div>
                    </div>
                </div>

                <!-- Interactive Chart Canvas Panels -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Revenue Bar Graph -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl border shadow-xs">
                        <h3 class="text-sm font-bold text-gray-800 mb-4">Revenue Trend (<?php echo $current_year; ?>)</h3>
                        <div class="h-64">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    <!-- Distribution Doughnut Graph -->
                    <div class="lg:col-span-1 bg-white p-6 rounded-xl border shadow-xs">
                        <h3 class="text-sm font-bold text-gray-800 mb-4">Bookings by Room Category</h3>
                        <div class="h-64 flex items-center justify-center">
                            <canvas id="typeChart"></canvas>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <footer class="bg-white border-t py-4 px-8 text-center text-xs font-medium text-gray-500">
            <span class="font-bold text-gray-800">Trio's Villa</span> &copy; 2026 All Rights Reserved.
        </footer>
    </div>

    <script>
        // Interactive Dropdown Menu Interface Controller Function
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('adminDropdownMenu');
            dropdown.classList.toggle('hidden');
        }

        // Global Event Click Hook to Close Dropdown Element on Outer Context Blur
        window.addEventListener('click', function() {
            const dropdown = document.getElementById('adminDropdownMenu');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        });

        // 1. Bar Chart Initialization for Earnings Trend
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_months); ?>,
                datasets: [{
                    label: 'Monthly Revenue (₹)',
                    data: <?php echo json_encode($chart_revenue); ?>,
                    backgroundColor: '#3b82f6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 2. Doughnut Chart Initialization for Category Demands
        const ctxType = document.getElementById('typeChart').getContext('2d');
        new Chart(ctxType, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($room_type_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($room_type_counts); ?>,
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ec4899', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>