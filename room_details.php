<?php
session_start();
require_once 'config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$room_id = intval($_GET['id']);

// 1. Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_review'])) {
    $guest_name = !empty($_SESSION['guest_name']) ? $_SESSION['guest_name'] : trim($_POST['guest_name'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');

    if (!empty($guest_name) && $rating >= 1 &&$rating <= 5 && !empty($comment)) {$conn->query("CREATE TABLE IF NOT EXISTS room_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            guest_name VARCHAR(100) NOT NULL,
            rating INT NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $rev_stmt =$conn->prepare("INSERT INTO room_reviews (room_id, guest_name, rating, comment) VALUES (?, ?, ?, ?)");
        $rev_stmt->bind_param("isis", $room_id,$guest_name, $rating,$comment);
        $rev_stmt->execute();$rev_stmt->close();
        
        header("Location: room_details.php?id=" . $room_id);
        exit();
    }
}

// 2. Fetch Room Details & Aggregated Rating
$room_stmt =$conn->prepare("
    SELECT r.*, 
           IFNULL(AVG(rv.rating), 0) as avg_rating, 
           COUNT(rv.id) as total_reviews,
           (SELECT COUNT(*) FROM bookings b 
            WHERE b.room_id = r.id 
            AND b.status = 'Approved' 
            AND b.check_out >= CURDATE()) as is_occupied
    FROM rooms r
    LEFT JOIN room_reviews rv ON r.id = rv.room_id
    WHERE r.id = ?
    GROUP BY r.id
");
$room_stmt->bind_param("i", $room_id);
$room_stmt->execute();$room = $room_stmt->get_result()->fetch_assoc();$room_stmt->close();

if (!$room) {
    header("Location: index.php");
    exit();
}

// 3. Fetch Slider Gallery Images with Smart Path & URL Resolution
$gallery_images = [];

// Query room_images for this room ID
$img_stmt = $conn->prepare("SELECT image_name FROM room_images WHERE room_id = ? ORDER BY id ASC");
if ($img_stmt) {
    $img_stmt->bind_param("i", $room_id);
    $img_stmt->execute();
    $img_res = $img_stmt->get_result();
    while ($img_row = $img_res->fetch_assoc()) {
        $img_val = trim($img_row['image_name'] ?? '');
        if (!empty($img_val)) {
            // Web URL (http/https)
            if (str_starts_with($img_val, 'http://') || str_starts_with($img_val, 'https://')) {
                $gallery_images[] = $img_val;
            } 
            // Local file stored in uploads/rooms/
            else {
                // Remove redundant 'uploads/rooms/' if it was stored with the path
                $clean_filename = basename($img_val);
                $gallery_images[] = 'uploads/rooms/' . $clean_filename;
            }
        }
    }
    $img_stmt->close();
}

// Add primary room image_url if available and not already in gallery
if (!empty($room['image_url'])) {
    $p = trim($room['image_url']);
    $primary_src = (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) 
        ? $p 
        : 'uploads/rooms/' . basename($p);

    if (!in_array($primary_src, $gallery_images)) {
        array_unshift($gallery_images, $primary_src);
    }
}

// Global fallback if no images exist anywhere
if (empty($gallery_images)) {
    $gallery_images[] = 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';
}

// 4. Fetch Review History Logs
$rev_stmt =$conn->prepare("SELECT * FROM room_reviews WHERE room_id = ? ORDER BY created_at DESC");
$rev_stmt->bind_param("i", $room_id);$rev_stmt->execute();
$reviews_query =$rev_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details - <?php echo htmlspecialchars($room['room_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <div>
        <?php if (file_exists(__DIR__ . '/config/navbar.php')) include 'config/navbar.php'; ?>

        <main class="max-w-5xl mx-auto px-4 sm:px-6 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm">
                
                <!-- Dynamic Image Carousel / Slider Container -->
                <div class="flex flex-col space-y-3">
                    <div class="relative w-full h-80 sm:h-96 rounded-xl overflow-hidden bg-gray-900 group">
                        <?php foreach ($gallery_images as $idx =>$img_src): ?>
                            <img 
                                src="<?php echo htmlspecialchars($img_src); ?>" 
                                alt="Room Image"
                                class="slider-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-300 ease-in-out <?php echo $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'; ?>"
                                data-index="<?php echo $idx; ?>"
                                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';"
                            >
                        <?php endforeach; ?>

                        <?php if (count($gallery_images) > 1): ?>
                            <!-- Controls: Previous & Next Buttons -->
                            <button type="button" onclick="prevSlide()" class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-gray-800 flex items-center justify-center shadow z-20 transition cursor-pointer">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                            <button type="button" onclick="nextSlide()" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-gray-800 flex items-center justify-center shadow z-20 transition cursor-pointer">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>

                            <!-- Slider Container -->
<div class="flex flex-col space-y-3">
    <div class="relative w-full h-80 sm:h-96 rounded-2xl overflow-hidden bg-slate-900 group shadow-sm">
        <?php foreach ($gallery_images as $idx => $img_src): ?>
            <img 
                src="<?php echo htmlspecialchars($img_src); ?>" 
                alt="Room Image"
                class="room-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-300 <?php echo $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>"
                data-slide-index="<?php echo $idx; ?>"
                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';"
            >
        <?php endforeach; ?>

        <?php if (count($gallery_images) > 1): ?>
            <button type="button" onclick="changeSlide(-1)" class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-slate-800 flex items-center justify-center shadow-md z-20 transition">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>
            <button type="button" onclick="changeSlide(1)" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-slate-800 flex items-center justify-center shadow-md z-20 transition">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
            <div class="absolute bottom-3 right-3 bg-black/60 backdrop-blur-xs text-white text-[11px] font-semibold px-2.5 py-1 rounded-md z-20">
                <span id="slideIndicator">1</span> / <?php echo count($gallery_images); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Thumbnails Strip -->
    <?php if (count($gallery_images) > 1): ?>
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <?php foreach ($gallery_images as $idx => $img_src): ?>
                <button type="button" onclick="selectSlide(<?php echo $idx; ?>)" class="thumb-node shrink-0 w-16 h-12 rounded-lg overflow-hidden border-2 transition <?php echo $idx === 0 ? 'border-blue-600 ring-2 ring-blue-100' : 'border-transparent opacity-60 hover:opacity-100'; ?>" data-thumb-index="<?php echo $idx; ?>">
                    <img src="<?php echo htmlspecialchars($img_src); ?>" class="w-full h-full object-cover" alt="Thumbnail">
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

                <!-- Specs Workspace Information -->
                <div class="flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-blue-600 font-bold uppercase tracking-wider bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">
                                <?php echo htmlspecialchars($room['room_type'] ?? 'Suite'); ?>
                            </span>
                            <?php if ($room['is_occupied'] > 0): ?>
                                <span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded font-bold">Currently Occupied</span>
                            <?php else: ?>
                                <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-bold">Available Now</span>
                            <?php endif; ?>
                        </div>

                        <!-- Star Scores element -->
                        <div class="flex items-center space-x-1.5 text-xs mb-3">
                            <div class="text-amber-500 flex">
                                <?php 
                                $rounded_rating = round((float)$room['avg_rating']);
                                for($i = 1; $i <= 5; $i++) {
                                    echo ($i <=$rounded_rating) ? '<i class="fa-solid fa-star mr-0.5"></i>' : '<i class="fa-regular fa-star text-gray-300 mr-0.5"></i>';
                                }
                                ?>
                            </div>
                            <span class="text-gray-400 font-medium">(<?php echo (int)$room['total_reviews']; ?> Reviews)</span>
                        </div>

                        <h1 class="text-2xl font-extrabold text-blue-600">Room <?php echo htmlspecialchars($room['room_number']); ?></h1>
                        <p class="text-xl font-bold text-gray-900 mt-2">₹<?php echo number_format((float)$room['price_per_night'], 2); ?> <span class="text-xs text-gray-400 font-normal">/ Night</span></p>
                        
                        <p class="text-gray-600 text-sm mt-4 leading-relaxed">
                            <?php echo !empty($room['description']) ? htmlspecialchars($room['description']) : "Welcome to our premier space option listing. Experience fully equipped premium amenities, structural insulation configurations, and comfort built specifically to match premium luxury standards."; ?>
                        </p>
                    </div>

                    <!-- Booking action trigger terminal -->
                    <div>
                        <?php if ($room['is_occupied'] > 0): ?>
                            <button disabled class="w-full text-center bg-gray-200 text-gray-400 font-medium py-3 rounded-lg cursor-not-allowed text-sm shadow-inner">
                                Room Reserved / Occupied
                            </button>
                        <?php else: ?>
                            <a href="index.php?open_modal=<?php echo $room['id']; ?>&room_name=<?php echo urlencode('Room ' .$room['room_number']); ?>#rooms" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition text-sm shadow-md">
                                Reserve Room Spaces
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Feedback Interaction Terminal Workspace Section -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12 border-t pt-8">
                <!-- Submission card input form panel -->
                <div class="md:col-span-1 bg-white p-5 rounded-xl border border-gray-100 shadow-xs h-fit">
                    <h3 class="text-sm font-bold text-gray-800 mb-3">Leave a Review</h3>
                    <form method="POST" class="space-y-3">
                        <input type="hidden" name="submit_review" value="1">
                        
                        <?php if (!isset($_SESSION['guest_logged_in'])): ?>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Your Name</label>
                                <input type="text" name="guest_name" required placeholder="John Doe" class="w-full mt-1 p-2 border text-xs rounded focus:outline-blue-500">
                            </div>
                        <?php else: ?>
                            <p class="text-xs text-gray-500 font-medium">Posting as: <span class="font-bold text-gray-900"><?php echo htmlspecialchars($_SESSION['guest_name']); ?></span></p>
                        <?php endif; ?>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Rating</label>
                            <div class="flex items-center space-x-1 text-gray-300 text-lg">
                                <input type="hidden" name="rating" id="ratingValue" value="5">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa-solid fa-star cursor-pointer text-amber-500 review-star-btn" data-value="<?php echo $i; ?>" onclick="setStarRating(<?php echo $i; ?>)"></i>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Comment</label>
                            <textarea name="comment" rows="3" required placeholder="Share your experience stay details..." class="w-full mt-1 p-2 border text-xs rounded focus:outline-blue-500 resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded text-xs transition cursor-pointer">Submit Review</button>
                    </form>
                </div>

                <!-- Review Feed Column Display -->
                <div class="md:col-span-2 space-y-4">
                    <h3 class="text-sm font-bold text-gray-800 mb-2">Guest Reviews History</h3>
                    <?php if ($reviews_query &&$reviews_query->num_rows > 0): ?>
                        <?php while($rev =$reviews_query->fetch_assoc()): ?>
                            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-bold text-sm text-gray-900"><?php echo htmlspecialchars($rev['guest_name']); ?></span>
                                    <span class="text-[10px] text-gray-400 font-medium"><?php echo date('d M Y', strtotime($rev['created_at'])); ?></span>
                                </div>
                                <div class="flex text-amber-500 text-xs mb-2">
                                    <?php for($x=1; $x<=5; $x++) {
                                        echo ($x <=$rev['rating']) ? '<i class="fa-solid fa-star mr-0.5"></i>' : '<i class="fa-regular fa-star text-gray-200 mr-0.5"></i>';
                                    } ?>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed"><?php echo htmlspecialchars($rev['comment']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-400 text-xs py-6 text-center italic border border-dashed rounded-xl bg-white">No guest reviews registered for this room category yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Interactive Carousel & Review Scripts -->
   <script>
let activeSlideIndex = 0;
const allSlides = document.querySelectorAll('.room-slide');
const allThumbs = document.querySelectorAll('.thumb-node');
const slideCounter = document.getElementById('slideIndicator');

function showSlide(index) {
    if (!allSlides.length) return;
    if (index >= allSlides.length) activeSlideIndex = 0;
    else if (index < 0) activeSlideIndex = allSlides.length - 1;
    else activeSlideIndex = index;

    allSlides.forEach((slide, i) => {
        if (i === activeSlideIndex) {
            slide.classList.remove('opacity-0', 'z-0');
            slide.classList.add('opacity-100', 'z-10');
        } else {
            slide.classList.add('opacity-0', 'z-0');
            slide.classList.remove('opacity-100', 'z-10');
        }
    });

    allThumbs.forEach((thumb, i) => {
        if (i === activeSlideIndex) {
            thumb.classList.add('border-blue-600', 'ring-2', 'ring-blue-100');
            thumb.classList.remove('border-transparent', 'opacity-60');
        } else {
            thumb.classList.remove('border-blue-600', 'ring-2', 'ring-blue-100');
            thumb.classList.add('border-transparent', 'opacity-60');
        }
    });

    if (slideCounter) {
        slideCounter.textContent = activeSlideIndex + 1;
    }
}

function changeSlide(direction) {
    showSlide(activeSlideIndex + direction);
}

function selectSlide(index) {
    showSlide(index);
}
</script>

    <?php if (file_exists(__DIR__ . '/config/footer.php')) include 'config/footer.php'; ?>
</body>
</html>