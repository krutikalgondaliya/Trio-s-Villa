<?php
session_start();
include 'config/db.php';

// Get category filter if set
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';

// Fetch gallery items safely based on category filter
if ($category !== 'all') {
    $stmt = $conn->prepare("SELECT * FROM gallery WHERE LOWER(category) = LOWER(?) ORDER BY id DESC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans flex flex-col min-h-screen justify-between">

    <div>
        <?php include 'config/navbar.php'; ?>

        <!-- Hero Title Banner -->
        <section class="bg-slate-900 text-white py-12 text-center">
            <h1 class="text-3xl md:text-4xl font-extrabold">Photo Gallery</h1>
            <p class="text-xs md:text-sm text-gray-400 mt-2">Explore the beauty and luxury of Trio's Villa</p>
        </section>

        <main class="max-w-6xl mx-auto px-6 py-12">
            <!-- Category Filters matching your interface -->
            <div class="flex justify-center items-center space-x-3 mb-10 overflow-x-auto py-2">
                <a href="gallery.php?category=all" 
                   class="px-5 py-2 rounded-xl text-sm font-semibold transition <?php echo ($category === 'all') ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-blue-600 border border-blue-500 hover:bg-blue-50'; ?>">
                   All Photos
                </a>
                <a href="gallery.php?category=Rooms" 
                   class="px-5 py-2 rounded-xl text-sm font-semibold transition <?php echo (strcasecmp($category, 'Rooms') === 0) ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-blue-600 border border-blue-500 hover:bg-blue-50'; ?>">
                   Rooms
                </a>
                <a href="gallery.php?category=Lobby" 
                   class="px-5 py-2 rounded-xl text-sm font-semibold transition <?php echo (strcasecmp($category, 'Lobby') === 0) ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-blue-600 border border-blue-500 hover:bg-blue-50'; ?>">
                   Lobby
                </a>
                <a href="gallery.php?category=Restaurant" 
                   class="px-5 py-2 rounded-xl text-sm font-semibold transition <?php echo (strcasecmp($category, 'Restaurant') === 0) ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-blue-600 border border-blue-500 hover:bg-blue-50'; ?>">
                   Restaurant
                </a>
                <a href="gallery.php?category=Pool" 
                   class="px-5 py-2 rounded-xl text-sm font-semibold transition <?php echo (strcasecmp($category, 'Pool') === 0 || strcasecmp($category, 'Swimming Pool') === 0) ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-blue-600 border border-blue-500 hover:bg-blue-50'; ?>">
                   Swimming Pool
                </a>
                <a href="gallery.php?category=Gym" 
                   class="px-5 py-2 rounded-xl text-sm font-semibold transition <?php echo (strcasecmp($category, 'Gym') === 0) ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-blue-600 border border-blue-500 hover:bg-blue-50'; ?>">
                   Gym
                </a>
                <a href="gallery.php?category=Bar" 
                   class="px-5 py-2 rounded-xl text-sm font-semibold transition <?php echo (strcasecmp($category, 'Bar') === 0) ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-blue-600 border border-blue-500 hover:bg-blue-50'; ?>">
                   Bar
                </a>
            </div>

            <!-- Image Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        // Safely retrieve image source (checks image_name, image_url, or image)
                        $img_src = $row['image_name'] ?? ($row['image_url'] ?? ($row['image'] ?? ''));
                        
                        // Format relative path if image filename is stored
                        if ($img_src && !filter_var($img_src, FILTER_VALIDATE_URL) && strpos($img_src, 'uploads/') === false) {
                            $img_src = 'uploads/gallery/' . $img_src;
                        }

                        // Safely retrieve title/caption
                        $caption = $row['caption'] ?? ($row['title'] ?? "Trio's Villa");
                    ?>
                        <div class="relative group overflow-hidden rounded-xl shadow-md bg-gray-100 cursor-pointer h-64" onclick="openLightbox('<?php echo htmlspecialchars($img_src); ?>', '<?php echo htmlspecialchars(addslashes($caption)); ?>')">
                            <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($caption); ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-end p-4">
                                <p class="text-white text-sm font-semibold"><?php echo htmlspecialchars($caption); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-16 text-gray-400 text-sm">
                        No photos uploaded in this category yet.
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Fullscreen Lightbox Modal -->
    <div id="lightboxModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4">
        <div class="relative max-w-4xl w-full flex flex-col items-center">
            <button onclick="closeLightbox()" class="absolute -top-10 right-0 text-white text-3xl font-bold hover:text-gray-300">&times;</button>
            <img id="lightboxImg" src="" class="max-h-[80vh] w-auto rounded-lg shadow-2xl object-contain">
            <p id="lightboxCaption" class="text-white text-sm font-medium mt-4 text-center"></p>
        </div>
    </div>

    <script>
        function openLightbox(src, caption) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('lightboxCaption').innerText = caption;
            document.getElementById('lightboxModal').style.display = 'flex';
        }

        function closeLightbox() {
            document.getElementById('lightboxModal').style.display = 'none';
        }

        // Close Lightbox on Escape Key Press
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>

    <?php include 'config/footer.php'; ?>
</body>
</html>