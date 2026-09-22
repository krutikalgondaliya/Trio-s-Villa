<?php
include 'config/db.php';

$category = $_GET['category'] ?? 'all';
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';

// Dynamically build the correct web-accessible URL for any room image
function getRoomImageSrc($rawName) {
    $fallback = 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80';

    if (empty($rawName)) {
        return $fallback;
    }

    if (str_starts_with($rawName, 'http://') || str_starts_with($rawName, 'https://')) {
        return $rawName;
    }

    $clean = basename(trim($rawName));

    // Check physical file location relative to project root
    if (file_exists(__DIR__ . '/uploads/rooms/' . $clean)) {
        return 'uploads/rooms/' . rawurlencode($clean);
    }

    if (file_exists(__DIR__ . '/project/uploads/rooms/' . $clean)) {
        return 'project/uploads/rooms/' . rawurlencode($clean);
    }

    // Default clean path
    return 'uploads/rooms/' . rawurlencode($clean);
}

$sql = "
    SELECT 
        r.*,
        COALESCE(AVG(rev.rating), 0) AS avg_rating,
        COUNT(rev.id) AS total_reviews
    FROM rooms r
    LEFT JOIN reviews rev ON r.id = rev.room_id AND rev.status = 'approved'
";

$where_clauses = [];
$params = [];
$types = "";

if ($category !== 'all' && !empty($category)) {
    $where_clauses[] = "r.room_type = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($check_in) && !empty($check_out)) {
    $where_clauses[] = "r.id NOT IN (
        SELECT room_id FROM bookings 
        WHERE status = 'Approved' 
        AND (
            (check_in <= ? AND check_out >= ?) OR
            (check_in <= ? AND check_out >= ?) OR
            (? <= check_in AND ? >= check_out)
        )
    )";
    $params[] = $check_in;
    $params[] = $check_in;
    $params[] = $check_out;
    $params[] = $check_out;
    $params[] = $check_in;
    $params[] = $check_out;
    $types .= "ssssss";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " GROUP BY r.id ORDER BY r.room_number ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$fallback_placeholder = 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80';

if ($result && $result->num_rows > 0):
    while ($room = $result->fetch_assoc()):
        $room_id = (int)$room['id'];
        
        // Fetch all uploaded slider images for this room
        $img_query = $conn->prepare("SELECT image_name FROM room_images WHERE room_id = ? ORDER BY id ASC");
        $img_query->bind_param("i", $room_id);
        $img_query->execute();
        $images = $img_query->get_result()->fetch_all(MYSQLI_ASSOC);
        $img_query->close();

        // Fallback to room's main image_url if room_images table is empty
        if (empty($images) && !empty($room['image_url'])) {
            $images[] = ['image_name' => $room['image_url']];
        }

        $rating = round($room['avg_rating']);
?>
    <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 flex flex-col justify-between transition hover:shadow-md">
        <!-- Room Slider / Image Container -->
        <div class="relative h-60 w-full overflow-hidden bg-gray-900 room-slider-container group select-none" data-current="0">
            <?php if (!empty($images)): ?>
                <div class="slider-track w-full h-full relative">
                    <?php foreach ($images as $index => $img): 
                        $src = getRoomImageSrc($img['image_name']);
                    ?>
                        <img 
                            src="<?php echo htmlspecialchars($src); ?>" 
                            class="slide absolute inset-0 w-full h-full object-cover" 
                            style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;"
                            alt="Room <?php echo htmlspecialchars($room['room_number']); ?>" 
                            onerror="this.onerror=null; this.src='<?php echo $fallback_placeholder; ?>';"
                        >
                    <?php endforeach; ?>
                </div>

                <?php if (count($images) > 1): ?>
                    <button 
                        type="button" 
                        onclick="prevSlide(this, event)" 
                        class="absolute left-2.5 top-1/2 -translate-y-1/2 bg-black/60 hover:bg-black/90 text-white w-8 h-8 rounded-full flex items-center justify-center transition z-20 cursor-pointer shadow-md focus:outline-none"
                    >
                        <i class="fa-solid fa-chevron-left text-xs pointer-events-none"></i>
                    </button>
                    <button 
                        type="button" 
                        onclick="nextSlide(this, event)" 
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 bg-black/60 hover:bg-black/90 text-white w-8 h-8 rounded-full flex items-center justify-center transition z-20 cursor-pointer shadow-md focus:outline-none"
                    >
                        <i class="fa-solid fa-chevron-right text-xs pointer-events-none"></i>
                    </button>

                    <!-- Slide counter badge -->
                    <div class="absolute bottom-2.5 right-3 z-20 bg-black/60 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full pointer-events-none">
                        <span class="slide-indicator">1</span>/<?php echo count($images); ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <img 
                    src="<?php echo $fallback_placeholder; ?>" 
                    class="w-full h-full object-cover" 
                    alt="Room <?php echo htmlspecialchars($room['room_number']); ?>"
                >
            <?php endif; ?>
        </div>

        <div class="p-6 flex-1 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Room <?php echo htmlspecialchars($room['room_number']); ?></h3>
                        <div class="flex items-center gap-1.5 mt-1">
                            <div class="text-amber-400 text-xs flex">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-<?php echo $i <= $rating ? 'solid' : 'regular'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-xs text-gray-500 font-medium">
                                (<?php echo $room['total_reviews'] > 0 ? number_format($room['avg_rating'], 1) . ' • ' . $room['total_reviews'] . ' reviews' : 'No reviews'; ?>)
                            </span>
                        </div>
                    </div>

                    <span class="bg-blue-50 text-blue-600 text-xs font-semibold px-3 py-1 rounded-full border border-blue-100">
                        <?php echo htmlspecialchars($room['room_type']); ?>
                    </span>
                </div>

                <p class="text-gray-500 text-sm mt-3">Comfortable and elegant accommodation at Trio's Villa.</p>
            </div>

            <div>
                <hr class="my-4 border-gray-100">

                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xl font-extrabold text-gray-900">₹<?php echo number_format($room['price_per_night'], 2); ?></span>
                        <span class="text-xs text-gray-400 font-medium">/ night</span>
                    </div>
                    <button onclick="openBookingModal(<?php echo $room['id']; ?>, 'Room <?php echo htmlspecialchars(addslashes($room['room_number'])); ?>', <?php echo $room['price_per_night']; ?>)" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition shadow-xs cursor-pointer">
                        Book Now
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php
    endwhile;
else:
?>
    <div class="col-span-3 text-center py-12 text-gray-400 text-sm">
        No rooms available matching the selected criteria.
    </div>
<?php
endif;
$stmt->close();
?>