<?php 
session_start();
include 'config/db.php'; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_room'])) {
    $room_id = intval($_POST['room_id']);
    $guest_name = trim($_POST['guest_name']);
    $guest_email = trim($_POST['guest_email']);
    $check_in =$_POST['check_in'];
    $check_out =$_POST['check_out'];
    $applied_coupon = trim($_POST['applied_coupon'] ?? '');

    $check_stmt =$conn->prepare("
        SELECT * FROM bookings 
        WHERE room_id = ? 
        AND status = 'Approved'
        AND (
            (check_in <= ? AND check_out >= ?) OR
            (check_in <= ? AND check_out >= ?) OR
            (? <= check_in AND ? >= check_out)
        )
    ");
    $check_stmt->bind_param("issssss", $room_id,$check_in, $check_in,$check_out, $check_out,$check_in, $check_out);$check_stmt->execute();
    $result =$check_stmt->get_result();

    if ($result->num_rows > 0) {$message = "<script>alert('Sorry, this room is already booked for the selected dates.');</script>";
    } else {
        $stmt =$conn->prepare("INSERT INTO bookings (room_id, guest_name, guest_email, check_in, check_out) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $room_id, $guest_name,$guest_email, $check_in,$check_out);
        
        if ($stmt->execute()) {
            $booking_id =$conn->insert_id;
            if(!empty($applied_coupon)) {$_SESSION['applied_coupon_'.$booking_id] =$applied_coupon;
            }
            header("Location: checkout.php?id=" . $booking_id);
            exit();
        } else {
            $message = "<script>alert('Error processing booking.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col justify-between">
    <div>
        <?php echo $message; ?>
        <?php if (file_exists('config/navbar.php')) include 'config/navbar.php'; ?>

        <!-- Video Background Header Section -->
        <header class="relative h-[480px] overflow-hidden flex items-center justify-center text-center text-white">
            <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover z-0">
                <source src="assets/videos/i_have_a_project_named_Trio_s.mp4" type="video/mp4">
                Your browser does not support HTML5 video.
            </video>
            
            <div class="absolute inset-0 bg-black/40 z-10"></div>

            <div class="relative z-20 max-w-2xl px-6">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-2 tracking-tight">Trio's Villa</h1>
                <p class="text-lg font-light mb-6 text-gray-100">Where Comfort Meets Elegance</p>
                <a href="#rooms" class="inline-block bg-amber-500 hover:bg-amber-600 text-gray-950 font-semibold px-6 py-3 rounded-md transition shadow-md">Book Your Stay</a>
            </div>
        </header>

        <!-- Search Bar Section -->
        <section class="max-w-4xl mx-auto px-6 -mt-10 relative z-20">
            <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide">Check-In Date</label>
                    <input type="date" id="searchCheckIn" min="<?php echo date('Y-m-d'); ?>" onchange="loadRooms()" class="w-full mt-1 p-2 text-sm border rounded focus:outline-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide">Check-Out Date</label>
                    <input type="date" id="searchCheckOut" min="<?php echo date('Y-m-d'); ?>" onchange="loadRooms()" class="w-full mt-1 p-2 text-sm border rounded focus:outline-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="clearDateFilters()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2 rounded border transition cursor-pointer">Reset Dates</button>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="max-w-6xl mx-auto px-6 py-16">
            <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-12 tracking-tight">Why Choose Us</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl border border-gray-100 shadow-sm text-center transition duration-300 hover:shadow-md hover:-translate-y-1">
                    <div class="flex justify-center mb-5 text-blue-600">
                        <i class="fa-solid fa-bed text-5xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-950 mb-3">Luxury Rooms</h3>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-xs mx-auto">Comfortable and elegant rooms for every guest.</p>
                </div>
                <div class="bg-white p-8 rounded-xl border border-gray-100 shadow-sm text-center transition duration-300 hover:shadow-md hover:-translate-y-1">
                    <div class="flex justify-center mb-5 text-emerald-700">
                        <i class="fa-solid fa-wifi text-5xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-950 mb-3">Free WiFi</h3>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-xs mx-auto">High-speed internet throughout the hotel premises.</p>
                </div>
                <div class="bg-white p-8 rounded-xl border border-gray-100 shadow-sm text-center transition duration-300 hover:shadow-md hover:-translate-y-1">
                    <div class="flex justify-center mb-5 text-rose-500">
                        <i class="fa-solid fa-utensils text-5xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-950 mb-3">Premium Restaurant</h3>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-xs mx-auto">Delicious local and international meals prepared by expert chefs.</p>
                </div>
            </div>
        </section>

        <!-- Featured Rooms Section -->
        <section id="rooms" class="max-w-6xl mx-auto px-6 py-8">
            <h2 class="text-3xl font-bold text-center mb-4 text-gray-900">Featured Rooms</h2>
            
            <div class="flex justify-center space-x-2 mb-10 overflow-x-auto py-2">
                <button type="button" onclick="changeCategory(this, 'all')" class="filter-btn bg-blue-600 text-white px-4 py-1.5 rounded-full text-sm font-medium transition shadow-sm cursor-pointer">All Rooms</button>
                <button type="button" onclick="changeCategory(this, 'Suite')" class="filter-btn bg-white text-gray-600 border px-4 py-1.5 rounded-full text-sm font-medium transition hover:bg-gray-50 cursor-pointer">Suites</button>
                <button type="button" onclick="changeCategory(this, 'Executive')" class="filter-btn bg-white text-gray-600 border px-4 py-1.5 rounded-full text-sm font-medium transition hover:bg-gray-50 cursor-pointer">Executive</button>
                <button type="button" onclick="changeCategory(this, 'Super Deluxe')" class="filter-btn bg-white text-gray-600 border px-4 py-1.5 rounded-full text-sm font-medium transition hover:bg-gray-50 cursor-pointer">Super Deluxe</button>
            </div>

            <!-- AJAX Rooms Grid Target -->
            <div id="roomsGrid" class="grid grid-cols-1 md:grid-cols-3 gap-8"></div>
        </section>

        <!-- Booking Modal -->
        <div id="bookingModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl max-w-md w-full p-6 relative shadow-2xl">
                <button type="button" onclick="closeBookingModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold cursor-pointer">&times;</button>
                <h3 class="text-xl font-bold mb-4 text-gray-900">Book <span id="modalRoomName"></span></h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="room_id" id="modalRoomId">
                    <input type="hidden" name="applied_coupon" id="appliedCouponHidden">
                    <input type="hidden" id="modalRoomPrice" value="0">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your Name</label>
                        <input type="text" name="guest_name" value="<?php echo isset($_SESSION['guest_name']) ? htmlspecialchars($_SESSION['guest_name']) : ''; ?>" required class="w-full mt-1 p-2 border rounded text-sm focus:outline-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="guest_email" value="<?php echo isset($_SESSION['guest_email']) ? htmlspecialchars($_SESSION['guest_email']) : ''; ?>" required class="w-full mt-1 p-2 border rounded text-sm focus:outline-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Check-In</label>
                            <input type="date" name="check_in" id="modal_check_in" min="<?php echo date('Y-m-d'); ?>" required class="w-full mt-1 p-2 border rounded text-sm focus:outline-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Check-Out</label>
                            <input type="date" name="check_out" id="modal_check_out" min="<?php echo date('Y-m-d'); ?>" required class="w-full mt-1 p-2 border rounded text-sm focus:outline-blue-500">
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-100">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Have a Promo Code?</label>
                        <div class="flex space-x-2">
                            <input type="text" id="couponCodeInput" placeholder="e.g. SUMMER20" class="flex-1 p-2 border rounded text-sm uppercase focus:outline-blue-500">
                            <button type="button" onclick="verifyPromoCode()" class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold px-4 py-2 rounded transition cursor-pointer">Apply</button>
                        </div>
                        <p id="couponMsg" class="text-xs mt-1.5 hidden font-medium"></p>
                    </div>

                    <button type="submit" name="book_room" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded font-semibold text-sm transition shadow cursor-pointer">Confirm Booking</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        let currentCategory = 'all';

        function loadRooms() {
            const grid = document.getElementById('roomsGrid');
            const checkIn = document.getElementById('searchCheckIn').value;
            const checkOut = document.getElementById('searchCheckOut').value;
            
            document.getElementById('modal_check_in').value = checkIn;
            document.getElementById('modal_check_out').value = checkOut;

            grid.innerHTML = '<div class="col-span-3 text-center py-12 text-gray-400 text-sm"><i class="fa-solid fa-spinner animate-spin mr-2"></i>Loading rooms...</div>';

            fetch(`fetch_rooms.php?category=${encodeURIComponent(currentCategory)}&check_in=${encodeURIComponent(checkIn)}&check_out=${encodeURIComponent(checkOut)}`)
                .then(response => response.text())
                .then(html => {
                    grid.innerHTML = html;
                })
                .catch(err => {
                    grid.innerHTML = '<div class="col-span-3 text-center py-12 text-red-500 text-sm">Error loading room records.</div>';
                });
        }

        function changeCategory(btnElement, category) {
            currentCategory = category;
            
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
                btn.classList.add('bg-white', 'text-gray-600', 'border');
            });

            btnElement.classList.remove('bg-white', 'text-gray-600', 'border');
            btnElement.classList.add('bg-blue-600', 'text-white', 'shadow-sm');

            loadRooms();
        }

        function clearDateFilters() {
            document.getElementById('searchCheckIn').value = '';
            document.getElementById('searchCheckOut').value = '';
            loadRooms();
        }

        function openBookingModal(id, name, price = 3500) {
            document.getElementById('modalRoomId').value = id;
            document.getElementById('modalRoomName').innerText = name;
            document.getElementById('modalRoomPrice').value = price;
            document.getElementById('appliedCouponHidden').value = '';
            document.getElementById('couponCodeInput').value = '';
            
            const msg = document.getElementById('couponMsg');
            msg.classList.add('hidden');
            msg.innerText = '';

            document.getElementById('bookingModal').style.display = 'flex';
        }
        
        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        function verifyPromoCode() {
            const code = document.getElementById('couponCodeInput').value.trim();
            const price = document.getElementById('modalRoomPrice').value;
            const msg = document.getElementById('couponMsg');

            if (!code) {
                msg.className = "text-xs mt-1.5 font-medium text-red-500";
                msg.innerText = "Please enter a promo code.";
                msg.classList.remove('hidden');
                return;
            }

            fetch(`apply_coupon.php?code=${encodeURIComponent(code)}&total_price=${price}`)
                .then(res => res.json())
                .then(data => {
                    msg.classList.remove('hidden');
                    if (data.success) {
                        msg.className = "text-xs mt-1.5 font-bold text-emerald-600";
                        msg.innerText = `${data.message} Saved ₹${data.discount}!`;
                        document.getElementById('appliedCouponHidden').value = code;
                    } else {
                        msg.className = "text-xs mt-1.5 font-medium text-red-500";
                        msg.innerText = data.message;
                        document.getElementById('appliedCouponHidden').value = '';
                    }
                })
                .catch(err => {
                    msg.className = "text-xs mt-1.5 font-medium text-red-500";
                    msg.innerText = "Error verifying coupon code.";
                    msg.classList.remove('hidden');
                });
        }

        // --- SLIDER CONTROLLERS MATCHING fetch_rooms.php ---
        function changeSlide(container, newIndex) {
            if (!container) return;
            const slides = container.querySelectorAll('.slide');
            const total = slides.length;
            if (total <= 1) return;

            let current = parseInt(container.getAttribute('data-current')) || 0;

            if (newIndex >= total) newIndex = 0;
            else if (newIndex < 0) newIndex = total - 1;

            // Display selected slide and hide all others
            slides.forEach((slide, idx) => {
                slide.style.display = (idx === newIndex) ? 'block' : 'none';
            });

            container.setAttribute('data-current', newIndex);

            const indicator = container.querySelector('.slide-indicator');
            if (indicator) {
                indicator.innerText = (newIndex + 1);
            }
        }

        function nextSlide(btn, event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            const container = btn.closest('.room-slider-container');
            if (!container) return;
            let current = parseInt(container.getAttribute('data-current')) || 0;
            changeSlide(container, current + 1);
        }

        function prevSlide(btn, event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            const container = btn.closest('.room-slider-container');
            if (!container) return;
            let current = parseInt(container.getAttribute('data-current')) || 0;
            changeSlide(container, current - 1);
        }

        // Backward compatibility for moveRoomSlide
        function moveRoomSlide(roomId, direction, event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            const track = document.getElementById('slider-track-' + roomId);
            if (!track) return;
            const slides = track.querySelectorAll('.slide, .slide-room-' + roomId);
            const total = slides.length;
            if (total <= 1) return;

            let current = parseInt(track.getAttribute('data-current')) || 0;
            let next = (current + direction + total) % total;

            slides.forEach((slide, idx) => {
                slide.style.display = (idx === next) ? 'block' : 'none';
            });

            track.setAttribute('data-current', next);
            const badge = document.getElementById('slide-num-' + roomId) || track.querySelector('.slide-indicator');
            if (badge) {
                badge.innerText = (next + 1);
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            loadRooms();
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('open_modal') && urlParams.has('room_name')) {
                openBookingModal(urlParams.get('open_modal'), urlParams.get('room_name'));
            }
        });
    </script>
    <?php if (file_exists('config/footer.php')) include 'config/footer.php'; ?>
</body>
</html>