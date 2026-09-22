<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';

$message = "";

// Handle Review Approval / Rejection Status
if (isset($_GET['action']) && isset($_GET['id'])) {
    $review_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: reviews.php");
    exit();
}

$all_reviews = $conn->query("
    SELECT r.*, g.fullname, g.email, rm.room_number, rm.room_type 
    FROM reviews r 
    JOIN guest_users g ON r.user_id = g.id 
    LEFT JOIN rooms rm ON r.room_id = rm.id 
    ORDER BY r.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin Panel</title>
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

            <a href="reports.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-chart-line w-5 text-center text-slate-400 group-hover:text-indigo-400 transition"></i> 
                <span>Reports & Analytics</span>
            </a>

            <!-- SECTION: CONTENT & COMMUNICATIONS -->
            <p class="px-3 pt-4 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Content & Support</p>

            <a href="reviews.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg transition text-xs font-semibold sidebar-link-active group">
                <i class="fa-solid fa-star w-5 text-center transition"></i> 
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

    <!-- Main Content -->
    <div class="flex-1 flex flex-col pl-64 justify-between min-h-screen">
        <div>
            <header class="bg-white border-b px-8 py-4 flex justify-between items-center shadow-sm relative">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Guest Reviews & Ratings</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Moderate and approve guest testimonials</p>
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
                <div class="bg-white rounded-xl border shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase border-b">
                                    <th class="p-4">Guest</th>
                                    <th class="p-4">Rating</th>
                                    <th class="p-4">Review</th>
                                    <th class="p-4">Room</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-sm">
                                <?php if ($all_reviews && $all_reviews->num_rows > 0): ?>
                                    <?php while($row = $all_reviews->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-4">
                                            <p class="font-bold text-gray-800"><?php echo htmlspecialchars($row['fullname']); ?></p>
                                            <p class="text-xs text-gray-400"><?php echo htmlspecialchars($row['email']); ?></p>
                                        </td>
                                        <td class="p-4 text-amber-500 font-bold text-xs whitespace-nowrap">
                                            <?php echo str_repeat('⭐', $row['rating']); ?> (<?php echo $row['rating']; ?>/5)
                                        </td>
                                        <td class="p-4 max-w-xs text-gray-600 text-xs italic">
                                            "<?php echo htmlspecialchars($row['review_text']); ?>"
                                        </td>
                                        <td class="p-4 text-xs font-semibold text-gray-500 whitespace-nowrap">
                                            <?php echo $row['room_number'] ? "Room " . htmlspecialchars($row['room_number']) : 'General'; ?>
                                        </td>
                                        <td class="p-4 whitespace-nowrap">
                                            <?php if ($row['status'] === 'approved'): ?>
                                                <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded border border-emerald-100">Approved</span>
                                            <?php elseif ($row['status'] === 'pending'): ?>
                                                <span class="bg-amber-50 text-amber-700 text-xs font-bold px-2.5 py-1 rounded border border-amber-100">Pending</span>
                                            <?php else: ?>
                                                <span class="bg-red-50 text-red-700 text-xs font-bold px-2.5 py-1 rounded border border-red-100">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-center space-x-2 whitespace-nowrap">
                                            <?php if ($row['status'] !== 'approved'): ?>
                                                <a href="reviews.php?action=approve&id=<?php echo $row['id']; ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-2.5 py-1 rounded transition" title="Approve">
                                                    <i class="fa-solid fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($row['status'] !== 'rejected'): ?>
                                                <a href="reviews.php?action=reject&id=<?php echo $row['id']; ?>" class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-2.5 py-1 rounded transition" title="Reject">
                                                    <i class="fa-solid fa-ban"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="reviews.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this review permanently?');" class="bg-red-600 hover:bg-red-700 text-white text-xs px-2.5 py-1 rounded transition" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="p-8 text-center text-gray-400 text-sm">No reviews found.</td></tr>
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