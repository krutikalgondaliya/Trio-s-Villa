<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

// Handle Delete Room Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    
    // Delete linked physical image files from disk
    $img_find = $conn->prepare("SELECT image_name FROM room_images WHERE room_id = ?");
    $img_find->bind_param("i", $delete_id);
    $img_find->execute();
    $img_res = $img_find->get_result();
    while ($img_row = $img_res->fetch_assoc()) {
        $raw_name = $img_row['image_name'];
        if (!str_starts_with($raw_name, 'http://') && !str_starts_with($raw_name, 'https://')) {
            $disk_file = __DIR__ . '/../uploads/rooms/' . basename($raw_name);
            if (file_exists($disk_file)) {
                @unlink($disk_file);
            }
        }
    }
    $img_find->close();

    // Delete records from database
    $conn->query("DELETE FROM room_images WHERE room_id = $delete_id");
    
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: rooms.php?msg=deleted");
        exit;
    } else {
        $error = "Failed to delete room: " . $conn->error;
    }
}

// Handle Add New Room Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    $room_number = trim($_POST['room_number'] ?? '');
    $room_type   = trim($_POST['room_type'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $image_url   = trim($_POST['image_url'] ?? '');

    if (empty($room_number) || empty($room_type) || empty($price)) {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a valid positive number.";
    } else {
        $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, price_per_night, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $room_number, $room_type, $price, $image_url);
        
        if ($stmt->execute()) {
            $new_room_id = $stmt->insert_id;

            // If image URL is provided, add it as the first slide image as well
            if (!empty($image_url)) {
                $ins_img = $conn->prepare("INSERT INTO room_images (room_id, image_name) VALUES (?, ?)");
                $ins_img->bind_param("is", $new_room_id, $image_url);
                $ins_img->execute();
                $ins_img->close();
            }

            header("Location: rooms.php?msg=added");
            exit;
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}

// Feedback alerts
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $message = "New room added successfully!";
    if ($_GET['msg'] === 'deleted') $message = "Room removed successfully!";
}

// Fetch rooms matching your database schema
$sql = "SELECT id, room_number, room_type, price_per_night, image_url FROM rooms ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-700 min-h-screen flex font-sans antialiased">

    <!-- Fixed Left Sidebar -->
    <aside class="w-64 bg-[#0A1124] text-slate-400 flex flex-col fixed inset-y-0 left-0 z-30 border-r border-[#15203B] select-none">
        <div class="p-5 flex items-center space-x-3.5 border-b border-[#15203B] shrink-0">
            <div class="w-10 h-10 rounded-xl bg-[#132247] text-blue-400 flex items-center justify-center border border-blue-500/20 shadow-sm">
                <i class="fa-solid fa-hotel text-lg"></i>
            </div>
            <div>
                <span class="text-sm font-extrabold tracking-wider text-white uppercase block leading-tight">TRIO'S VILLA</span>
                <span class="text-[10px] font-bold text-slate-500 tracking-widest uppercase">Admin Panel</span>
            </div>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 sidebar-scroll">
            <p class="px-3 pt-2 pb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Main</p>
            <a href="index.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-house w-5 text-center text-slate-500"></i> 
                <span>Dashboard</span>
            </a>

            <p class="px-3 pt-5 pb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Room Operations</p>
            <a href="room_types.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-layer-group w-5 text-center text-slate-500"></i>
                <span>Room Types</span>
            </a>
            <a href="rooms.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-bold text-white bg-[#132142] border-l-4 border-blue-500 shadow-sm transition">
                <i class="fa-solid fa-bed w-5 text-center text-blue-400"></i> 
                <span>Rooms Inventory</span>
            </a>

            <p class="px-3 pt-5 pb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Reservations & Guests</p>
            <a href="calendar.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-calendar-days w-5 text-center text-slate-500"></i> 
                <span>Reservation Calendar</span>
            </a>
            <a href="bookings.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-calendar-check w-5 text-center text-slate-500"></i> 
                <span>Bookings</span>
            </a>
            <a href="customers.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-users w-5 text-center text-slate-500"></i> 
                <span>Customers</span>
            </a>

            <p class="px-3 pt-5 pb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Finance & Marketing</p>
            <a href="payments.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-credit-card w-5 text-center text-slate-500"></i> 
                <span>Payments</span>
            </a>
            <a href="coupons.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-ticket w-5 text-center text-slate-500"></i> 
                <span>Promo Codes</span>
            </a>
            <a href="reports.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-chart-line w-5 text-center text-slate-500"></i> 
                <span>Reports & Analytics</span>
            </a>

            <p class="px-3 pt-5 pb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Content & Support</p>
            <a href="reviews.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-star w-5 text-center text-slate-500"></i> 
                <span>Guest Reviews</span>
            </a>
            <a href="gallery.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-image w-5 text-center text-slate-500"></i> 
                <span>Gallery</span>
            </a>
            <a href="messages.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 transition">
                <i class="fa-solid fa-envelope w-5 text-center text-slate-500"></i> 
                <span>Messages</span>
            </a>
        </nav>

        <div class="p-4 border-t border-[#15203B] shrink-0">
            <a href="logout.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-xs font-bold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition">
                <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i> 
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 ml-64 min-h-screen">
        
        <!-- Header with Dropdown -->
        <header class="h-20 bg-white border-b border-slate-200 px-8 flex items-center justify-between sticky top-0 z-20 shadow-xs">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Manage Rooms</h2>
                <p class="text-xs text-slate-400 mt-0.5">Configure hotel room inventory & slider images</p>
            </div>

            <div class="relative" id="adminDropdownContainer">
                <button type="button" onclick="toggleAdminMenu()" class="flex items-center space-x-3 focus:outline-none cursor-pointer group">
                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-sm shadow-sm transition group-hover:ring-2 group-hover:ring-blue-400">
                        A
                    </div>
                    <div class="text-left leading-tight hidden sm:block">
                        <span class="block text-sm font-bold text-slate-800">admin</span>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            Administrator <i class="fa-solid fa-chevron-down text-[8px] ml-1 transition-transform" id="adminDropdownArrow"></i>
                        </span>
                    </div>
                </button>

                <div id="adminDropdownMenu" class="hidden absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                    <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        <i class="fa-solid fa-house text-slate-400 w-4 text-center"></i>
                        <span>Dashboard Home</span>
                    </a>
                    <a href="reports.php" class="flex items-center gap-3 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        <i class="fa-solid fa-chart-line text-slate-400 w-4 text-center"></i>
                        <span>View Reports</span>
                    </a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition">
                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i>
                        <span>Logout System</span>
                    </a>
                </div>
            </div>
        </header>

        <main class="p-8 flex-1">
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Add New Room Form -->
                <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <h3 class="text-base font-bold text-slate-900 mb-6">Add New Room</h3>

                    <form action="rooms.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Room Number</label>
                            <input type="text" name="room_number" required placeholder="e.g. Room 101" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Room Type</label>
                            <div class="relative">
                                <select name="room_type" required class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-200 bg-white appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-slate-700">
                                    <option value="Suite">Suite</option>
                                    <option value="Executive">Executive</option>
                                    <option value="Super Deluxe">Super Deluxe</option>
                                    <option value="Standard">Standard</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-3.5 top-3.5 text-[10px] text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Price Per Night (₹)</label>
                            <input type="number" step="0.01" min="0" name="price" required placeholder="2000.00" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Image Resource URL</label>
                            <input type="text" name="image_url" placeholder="https://..." class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <button type="submit" name="add_room" class="w-full mt-2 py-3 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold rounded-lg shadow-sm transition cursor-pointer">
                            Add Room
                        </button>
                    </form>
                </div>

                <!-- Rooms Table -->
                <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                                    <th class="py-4 px-6">Photo</th>
                                    <th class="py-4 px-6">Details</th>
                                    <th class="py-4 px-6">Price</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <?php 
                                            $img = trim($row['image_url'] ?? '');
                                            if (empty($img)) {
                                                $img_src = 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=200&auto=format&fit=crop&q=80';
                                            } elseif (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                                                $img_src = $img;
                                            } else {
                                                $img_src = '../uploads/rooms/' . basename($img);
                                            }
                                        ?>
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="py-4 px-6">
                                                <img src="<?php echo htmlspecialchars($img_src); ?>" 
                                                     alt="Room" 
                                                     class="w-14 h-11 object-cover rounded-lg border border-slate-200 shadow-2xs"
                                                     onerror="this.src='https://images.unsplash.com/photo-1590490360182-c33d57733427?w=200&auto=format&fit=crop&q=80';">
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="font-bold text-slate-800 text-sm">
                                                    <?php echo htmlspecialchars($row['room_number']); ?>
                                                </div>
                                                <div class="text-[11px] text-slate-400 font-normal">
                                                    <?php echo htmlspecialchars($row['room_type']); ?>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6 font-semibold text-slate-700">
                                                ₹<?php echo number_format((float)($row['price_per_night'] ?? 0), 2); ?>
                                            </td>
                                            <td class="py-4 px-6">
                                                <span class="inline-block text-[11px] font-semibold px-2.5 py-0.5 rounded-full text-emerald-600 bg-emerald-50 border border-emerald-100">
                                                    Available
                                                </span>
                                            </td>
                                            <td class="py-4 px-6 text-center space-x-3">
                                                <a href="slider_images.php?room_id=<?php echo urlencode($row['id']); ?>" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700 transition">
                                                    <i class="fa-regular fa-images mr-1.5"></i> Slider Images
                                                </a>
                                                <a href="rooms.php?action=delete&id=<?php echo urlencode($row['id']); ?>" 
                                                   onclick="return confirm('Are you sure you want to delete room <?php echo htmlspecialchars(addslashes($row['room_number'])); ?>?');" 
                                                   class="inline-flex items-center text-xs font-semibold text-rose-600 hover:text-rose-700 transition">
                                                    Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                            No rooms in inventory. Add one on the left.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <footer class="py-4 px-8 text-center text-xs text-slate-400 border-t border-slate-200">
            Trio's Villa &copy; 2026 All Rights Reserved.
        </footer>
    </div>

    <script>
    function toggleAdminMenu() {
        const menu = document.getElementById('adminDropdownMenu');
        const arrow = document.getElementById('adminDropdownArrow');
        menu.classList.toggle('hidden');
        if (arrow) {
            arrow.classList.toggle('rotate-180');
        }
    }

    document.addEventListener('click', function(e) {
        const container = document.getElementById('adminDropdownContainer');
        const menu = document.getElementById('adminDropdownMenu');
        const arrow = document.getElementById('adminDropdownArrow');
        if (container && !container.contains(e.target) && menu && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            if (arrow) {
                arrow.classList.remove('rotate-180');
            }
        }
    });
    </script>
</body>
</html>