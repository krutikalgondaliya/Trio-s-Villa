<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

// Validate Room ID
if (!isset($_GET['room_id']) || !is_numeric($_GET['room_id'])) {
    header("Location: rooms.php");
    exit();
}

$room_id = (int)$_GET['room_id'];

// Fetch Room Information
$stmt = $conn->prepare("SELECT id, room_number, room_type FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$room) {
    header("Location: rooms.php");
    exit();
}

// Ensure the room_images table exists
$conn->query("CREATE TABLE IF NOT EXISTS room_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    image_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle Delete Image
if (isset($_GET['delete_img_id']) && is_numeric($_GET['delete_img_id'])) {
    $delete_id = (int)$_GET['delete_img_id'];
    
    // Find image to remove file from disk if locally stored
    $find = $conn->prepare("SELECT image_name FROM room_images WHERE id = ? AND room_id = ?");
    $find->bind_param("ii", $delete_id, $room_id);
    $find->execute();
    $img_row = $find->get_result()->fetch_assoc();
    $find->close();

    if ($img_row) {
        $img_name = $img_row['image_name'];
        if (!str_starts_with($img_name, 'http://') && !str_starts_with($img_name, 'https://')) {
            $disk_file = __DIR__ . '/../uploads/rooms/' . basename($img_name);
            if (file_exists($disk_file)) {
                @unlink($disk_file);
            }
        }

        $del_stmt = $conn->prepare("DELETE FROM room_images WHERE id = ? AND room_id = ?");
        $del_stmt->bind_param("ii", $delete_id, $room_id);
        $del_stmt->execute();
        $del_stmt->close();

        header("Location: slider_images.php?room_id=" . $room_id . "&msg=deleted");
        exit();
    }
}

// Handle Add Image (Via URL or File Upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $image_url = trim($_POST['image_url'] ?? '');

    // Case 1: Web Image URL provided
    if (!empty($image_url)) {
        $ins = $conn->prepare("INSERT INTO room_images (room_id, image_name) VALUES (?, ?)");
        $ins->bind_param("is", $room_id, $image_url);
        if ($ins->execute()) {
            $ins->close();
            header("Location: slider_images.php?room_id=" . $room_id . "&msg=added");
            exit();
        } else {
            $error = "Failed to save image URL: " . $conn->error;
        }
    } 
    // Case 2: File Upload provided
    elseif (isset($_FILES['room_file']) && $_FILES['room_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['room_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $upload_dir = __DIR__ . '/../uploads/rooms/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $unique_name = 'slider_' . $room_id . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $destination = $upload_dir . $unique_name;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $ins = $conn->prepare("INSERT INTO room_images (room_id, image_name) VALUES (?, ?)");
                $ins->bind_param("is", $room_id, $unique_name);
                $ins->execute();
                $ins->close();

                header("Location: slider_images.php?room_id=" . $room_id . "&msg=added");
                exit();
            } else {
                $error = "Could not move uploaded image file.";
            }
        } else {
            $error = "Only JPG, PNG, and WEBP formats are allowed.";
        }
    } else {
        $error = "Please provide an image URL or choose an image file to upload.";
    }
}

// Feedback alerts
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $message = "Slider image added successfully!";
    if ($_GET['msg'] === 'deleted') $message = "Image removed successfully!";
}

// Fetch all slider images for this room
$img_stmt = $conn->prepare("SELECT id, image_name, created_at FROM room_images WHERE room_id = ? ORDER BY id DESC");
$img_stmt->bind_param("i", $room_id);
$img_stmt->execute();
$slider_images = $img_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slider Images - Room <?php echo htmlspecialchars($room['room_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-[#F8FAFC] text-slate-700 min-h-screen font-sans antialiased">

    <!-- Top Navigation Header -->
    <header class="h-20 bg-white border-b border-slate-200 px-8 flex items-center justify-between sticky top-0 z-20 shadow-xs">
        <div class="flex items-center space-x-4">
            <a href="rooms.php" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Rooms
            </a>
            <span class="text-slate-300">|</span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">
                    Slider Images: <?php echo htmlspecialchars($room['room_number']); ?>
                </h1>
                <p class="text-xs text-slate-400">
                    Type: <?php echo htmlspecialchars($room['room_type']); ?>
                </p>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto p-8">
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Upload Box -->
            <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4">Add Slider Image</h2>
                
                <form action="slider_images.php?room_id=<?php echo $room_id; ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Image URL</label>
                        <input type="text" name="image_url" placeholder="https://..." class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="text-center text-xs font-semibold text-slate-400">— OR UPLOAD FILE —</div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Upload From Computer</label>
                        <input type="file" name="room_file" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>

                    <button type="submit" name="upload_image" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition cursor-pointer">
                        Add to Slider
                    </button>
                </form>
            </div>

            <!-- Images Gallery Grid -->
            <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4">Existing Slider Images</h2>
                
                <?php if ($slider_images && $slider_images->num_rows > 0): ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <?php while ($img = $slider_images->fetch_assoc()): ?>
                            <?php 
                                $src = $img['image_name'];
                                if (!str_starts_with($src, 'http://') && !str_starts_with($src, 'https://')) {
                                    $src = '../uploads/rooms/' . basename($src);
                                }
                            ?>
                            <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shadow-xs">
                                <img 
                                    src="<?php echo htmlspecialchars($src); ?>" 
                                    alt="Slider Image" 
                                    class="w-full h-36 object-cover"
                                    onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400&auto=format&fit=crop&q=80';"
                                >
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <a href="slider_images.php?room_id=<?php echo $room_id; ?>&delete_img_id=<?php echo $img['id']; ?>" 
                                       onclick="return confirm('Remove this image from the slider?');" 
                                       class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                                        <i class="fa-solid fa-trash-can text-xs"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 border-2 border-dashed border-slate-200 rounded-xl">
                        <i class="fa-regular fa-image text-4xl text-slate-300 mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No slider images uploaded for this room yet.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

</body>
</html>