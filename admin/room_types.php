<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';

$message = "";

// Handle Room Type Insertion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_type'])) {
    $type_name = trim($_POST['type_name']);
    $base_price = floatval($_POST['base_price']);

    if (!empty($type_name) && $base_price > 0) {
        $stmt = $conn->prepare("INSERT INTO room_types (type_name, base_price) VALUES (?, ?)");
        $stmt->bind_param("sd", $type_name, $base_price);
        if ($stmt->execute()) {
            $message = "<div class='bg-green-50 text-green-700 p-3 rounded text-sm mb-4 border border-green-100'>Room type added successfully!</div>";
        } else {
            $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Error adding room type.</div>";
        }
        $stmt->close();
    }
}

// Handle Room Type Deletion
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM room_types WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "<div class='bg-green-50 text-green-700 p-3 rounded text-sm mb-4 border border-green-100'>Room type deleted successfully!</div>";
    } else {
        $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Error deleting room type.</div>";
    }
    $stmt->close();
    header("Location: room_types.php");
    exit();
}

$types_result = $conn->query("SELECT * FROM room_types ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Types - Admin Panel</title>
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

            <a href="room_types.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg transition text-xs font-semibold sidebar-link-active group">
                <i class="fa-solid fa-layer-group w-5 text-center transition"></i> 
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
                    <h1 class="text-2xl font-bold text-gray-800">Room Categories</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Manage hotel structural class properties</p>
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

            <main class="p-8">
                <?php echo $message; ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-xl border shadow-xs h-fit">
                        <h3 class="text-base font-bold text-gray-800 mb-4">Create Tier Category</h3>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Category Name</label>
                                <input type="text" name="type_name" required placeholder="e.g. Presidential Suite" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Base Floor Price (₹)</label>
                                <input type="number" step="0.01" name="base_price" required placeholder="5000.00" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                            </div>
                            <button type="submit" name="add_type" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded text-sm transition">Save Category</button>
                        </form>
                    </div>

                    <div class="lg:col-span-2 bg-white rounded-xl border shadow-xs overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase border-b">
                                    <th class="p-4">ID</th>
                                    <th class="p-4">Category Name</th>
                                    <th class="p-4">Base Rate</th>
                                    <th class="p-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-sm">
                                <?php if ($types_result && $types_result->num_rows > 0): ?>
                                    <?php while($type = $types_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-4 text-gray-500 font-mono">#<?php echo $type['id']; ?></td>
                                        <td class="p-4 font-bold text-gray-900"><?php echo htmlspecialchars($type['type_name']); ?></td>
                                        <td class="p-4 font-medium text-gray-700">₹<?php echo number_format($type['base_price'], 2); ?></td>
                                        <td class="p-4 text-center">
                                            <a href="room_types.php?delete=<?php echo $type['id']; ?>" onclick="return confirm('Are you sure you want to delete this room type?');" class="text-red-600 hover:text-red-750 font-bold text-xs">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="p-8 text-center text-gray-400">No category classifications configured.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
        <footer class="bg-white border-t py-4 px-8 text-center text-xs font-medium text-gray-500">
            <span class="font-bold text-gray-800">Trio's Villa</span> &copy; 2026 All Rights Reserved.
        </footer>
    </div>

    <!-- Dropdown Control Logic Hook scripts -->
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