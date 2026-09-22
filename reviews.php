<?php
session_start();
include 'config/db.php';

$message = "";

// Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['guest_logged_in']) || $_SESSION['guest_logged_in'] !== true) {
        $message = "<div class='bg-amber-50 text-amber-700 p-3 rounded text-sm mb-4 border border-amber-100'>Please <a href='user_login.php' class='underline font-bold'>log in</a> to leave a review.</div>";
    } else {
        $user_id = $_SESSION['guest_id'] ?? 1; // Fallback if session key varies
        $room_id = !empty($_POST['room_id']) ? intval($_POST['room_id']) : null;
        $rating = intval($_POST['rating']);
        $review_text = trim($_POST['review_text']);

        if ($rating >= 1 && $rating <= 5 && !empty($review_text)) {
            $stmt = $conn->prepare("INSERT INTO reviews (user_id, room_id, rating, review_text, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iiis", $user_id, $room_id, $rating, $review_text);
            if ($stmt->execute()) {
                $message = "<div class='bg-green-50 text-green-700 p-3 rounded text-sm mb-4 border border-green-100'>Thank you! Your review has been submitted and is awaiting approval.</div>";
            } else {
                $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Failed to submit review. Please try again.</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm mb-4 border border-red-100'>Please provide a rating and review text.</div>";
        }
    }
}

// Fetch Approved Reviews
$approved_reviews = $conn->query("
    SELECT r.*, g.fullname, g.profile_pic, rm.room_type, rm.room_number 
    FROM reviews r 
    JOIN guest_users g ON r.user_id = g.id 
    LEFT JOIN rooms rm ON r.room_id = rm.id 
    WHERE r.status = 'approved' 
    ORDER BY r.id DESC
");

// Fetch rooms for dropdown
$rooms_list = $conn->query("SELECT id, room_number, room_type FROM rooms ORDER BY room_number ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Reviews - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <div>
        <?php include 'config/navbar.php'; ?>

        <!-- Hero Section -->
        <section class="bg-slate-900 text-white py-12 text-center">
            <h1 class="text-3xl font-bold">Guest Experiences & Reviews</h1>
            <p class="text-slate-400 text-sm mt-2">See what our guests have to say about their stay at Trio's Villa</p>
        </section>

        <main class="max-w-6xl mx-auto px-6 py-12">
            <?php echo $message; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Submit Review Form -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm h-fit">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-blue-600"></i> Write a Review
                    </h3>

                    <?php if (isset($_SESSION['guest_logged_in']) && $_SESSION['guest_logged_in'] === true): ?>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Room Visited (Optional)</label>
                                <select name="room_id" class="w-full mt-1 p-2 border text-sm rounded bg-white focus:outline-blue-500">
                                    <option value="">General Stay / Not Specified</option>
                                    <?php if ($rooms_list && $rooms_list->num_rows > 0): ?>
                                        <?php while($rm = $rooms_list->fetch_assoc()): ?>
                                            <option value="<?php echo $rm['id']; ?>">Room <?php echo htmlspecialchars($rm['room_number']); ?> (<?php echo htmlspecialchars($rm['room_type']); ?>)</option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Your Rating</label>
                                <select name="rating" required class="w-full mt-1 p-2 border text-sm rounded bg-white focus:outline-blue-500">
                                    <option value="5">⭐⭐⭐⭐⭐ (5 / 5 - Exceptional)</option>
                                    <option value="4">⭐⭐⭐⭐ (4 / 5 - Very Good)</option>
                                    <option value="3">⭐⭐⭐ (3 / 5 - Average)</option>
                                    <option value="2">⭐⭐ (2 / 5 - Poor)</option>
                                    <option value="1">⭐ (1 / 5 - Terrible)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase">Your Feedback</label>
                                <textarea name="review_text" rows="4" required placeholder="Tell us about your experience..." class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500"></textarea>
                            </div>

                            <button type="submit" name="submit_review" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded text-sm transition">
                                Submit Feedback
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-6 text-sm text-gray-500">
                            <i class="fa-solid fa-lock text-3xl text-gray-300 mb-3"></i>
                            <p>You must be signed in to submit a review.</p>
                            <a href="user_login.php" class="inline-block mt-4 bg-blue-600 text-white px-5 py-2 rounded text-xs font-semibold hover:bg-blue-700 transition">Log In Now</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Display Approved Reviews -->
                <div class="lg:col-span-2 space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Verified Guest Feedback</h3>
                    
                    <?php if ($approved_reviews && $approved_reviews->num_rows > 0): ?>
                        <?php while($rev = $approved_reviews->fetch_assoc()): ?>
                            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-xs">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 font-bold flex items-center justify-center border border-blue-100">
                                            <?php echo strtoupper(substr($rev['fullname'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($rev['fullname']); ?></h4>
                                            <p class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-amber-400 text-xs tracking-wider">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa-<?php echo $i <= $rev['rating'] ? 'solid' : 'regular'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <p class="text-sm text-gray-600 mt-3 italic">"<?php echo htmlspecialchars($rev['review_text']); ?>"</p>

                                <?php if ($rev['room_type']): ?>
                                    <div class="mt-3 text-xs text-blue-600 font-semibold inline-block bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                        Stayed in: Room <?php echo htmlspecialchars($rev['room_number']); ?> (<?php echo htmlspecialchars($rev['room_type']); ?>)
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bg-white p-8 rounded-xl border text-center text-gray-400 text-sm">
                            No approved reviews yet. Be the first to share your experience!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <?php include 'config/footer.php'; ?>
</body>
</html>