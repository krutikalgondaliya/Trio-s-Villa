<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';

$message = "";

// Ensure upload directory exists
$upload_dir = "../uploads/gallery/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle Uploading / Adding Gallery Image
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_image'])) {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $image_url = trim($_POST['image_url']);
    $final_image_path = "";

    // Priority 1: Handle File Upload from computer
    if (isset($_FILES['gallery_file']) && $_FILES['gallery_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gallery_file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['gallery_file']['name']);
        $target_file = $upload_dir . $file_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['gallery_file']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_types)) {
            if (move_uploaded_file($file_tmp, $target_file)) {
                $final_image_path = "uploads/gallery/" . $file_name;
            } else {
                $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Failed to upload file to server.</div>";
            }
        } else {
            $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Invalid file format. Only JPG, PNG, WEBP, and GIF are allowed.</div>";
        }
    } 
    // Priority 2: Use Image Resource URL if no file uploaded
    elseif (!empty($image_url)) {
        $final_image_path = $image_url;
    }

    // Save into database
    if (!empty($final_image_path) && empty($message)) {
        $stmt = $conn->prepare("INSERT INTO gallery (title, category, image_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $category, $final_image_path);
        if ($stmt->execute()) {
            $message = "<div class='bg-green-50 text-green-700 p-3 rounded text-sm mb-4 border border-green-100'>Gallery image added successfully!</div>";
        } else {
            $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Error adding gallery image to database.</div>";
        }
        $stmt->close();
    } elseif (empty($message)) {
        $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Please upload a file OR provide an image URL.</div>";
    }
}

// Handle Deleting Gallery Image
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    
    // Optionally remove local file if stored in uploads
    $get_img = $conn->prepare("SELECT image_url FROM gallery WHERE id = ?");
    $get_img->bind_param("i", $del_id);
    $get_img->execute();
    $res = $get_img->get_result()->fetch_assoc();
    if ($res && strpos($res['image_url'], 'uploads/gallery/') === 0) {
        $file_to_delete = "../" . $res['image_url'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
    $get_img->close();

    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    header("Location: gallery.php");
    exit();
}

$gallery_result = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Admin Panel</title>
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

            <a href="reviews.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition text-xs font-semibold text-slate-300 group">
                <i class="fa-solid fa-star w-5 text-center text-slate-400 group-hover:text-amber-400 transition"></i> 
                <span>Guest Reviews</span>
            </a>

            <a href="gallery.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg transition text-xs font-semibold sidebar-link-active group">
                <i class="fa-solid fa-image w-5 text-center transition"></i> 
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

    <!-- Right Side Main Content Area -->
    <div class="flex-1 flex flex-col pl-64 justify-between min-h-screen">
        <div>
            <header class="bg-white border-b px-8 py-4 flex justify-between items-center shadow-sm relative">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Manage Gallery</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Upload and control public showcase media</p>
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
                    <!-- Form Container -->
                    <div class="bg-white p-6 rounded-xl border shadow-xs h-fit">
                        <h3 class="text-base font-bold text-gray-800 mb-4">Add Gallery Image</h3>
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Image Title</label>
                                <input type="text" name="title" placeholder="e.g. Bar" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Category</label>
                                <select name="category" class="w-full mt-1 p-2 border text-sm rounded bg-white focus:outline-blue-500">
                                    <option value="Rooms">Rooms</option>
                                    <option value="Lobby">Lobby</option>
                                    <option value="Restaurant">Restaurant</option>
                                    <option value="Swimming Pool">Swimming Pool</option>
                                    <option value="Gym">Gym</option>
                                    <option value="Bar">Bar</option>
                                    <option value="General">General</option>
                                </select>
                            </div>

                            <!-- Upload Button Input -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Upload Image File</label>
                                <input type="file" name="gallery_file" accept="image/*" class="w-full mt-1 p-1.5 border text-xs rounded bg-gray-50 text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>

                            <div class="text-center text-xs font-bold text-gray-400 uppercase tracking-wide my-1">- OR -</div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Image Resource URL</label>
                                <input type="text" name="image_url" placeholder="https://..." class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                            </div>

                            <button type="submit" name="add_image" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded text-sm transition shadow-sm">Save Image</button>
                        </form>
                    </div>

                    <!-- Images Display Grid -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl border shadow-xs">
                        <h3 class="text-base font-bold text-gray-800 mb-4">Live Showcase Images</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php if ($gallery_result && $gallery_result->num_rows > 0): ?>
                                <?php while($img = $gallery_result->fetch_assoc()): 
                                    // Handle correct image paths for uploaded files vs web links
                                    $src = (strpos($img['image_url'], 'http') === 0) ? $img['image_url'] : '../' . $img['image_url'];
                                ?>
                                    <div class="relative group rounded-lg overflow-hidden border bg-gray-50">
                                        <img src="<?php echo htmlspecialchars($src); ?>" alt="Gallery Image" class="w-full h-40 object-cover">
                                        <div class="p-3">
                                            <h4 class="font-bold text-sm text-gray-800"><?php echo htmlspecialchars($img['title'] ?: 'Untitled'); ?></h4>
                                            <span class="text-xs text-blue-600 font-semibold bg-blue-50 px-2 py-0.5 rounded border border-blue-100 mt-1 inline-block"><?php echo htmlspecialchars($img['category']); ?></span>
                                        </div>
                                        <a href="gallery.php?delete=<?php echo $img['id']; ?>" onclick="return confirm('Delete this image?');" class="absolute top-2 right-2 bg-red-600 text-white w-7 h-7 flex items-center justify-center rounded-full opacity-0 group-hover:opacity-100 transition shadow">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="col-span-full text-center py-8 text-gray-400 text-sm">No images added to gallery yet.</p>
                            <?php endif; ?>
                        </div>
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