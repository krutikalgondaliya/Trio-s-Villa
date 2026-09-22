<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
    <?php include 'config/navbar.php'; ?>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="max-w-5xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-extrabold text-gray-900 mb-6">About Our Resort</h2>
                    <p class="text-gray-600 leading-relaxed mb-4">Welcome to a sanctuary of premium comfort and unmatched style. We dedicate our spaces to providing high-class hospitality services tailored directly to weekend vacationers and business travelers alike.</p>
                    <p class="text-gray-600 leading-relaxed">Enjoy our curated spaces, friendly around-the-clock room service management, and world-class dining lounges custom-fit for your comfort.</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=400&q=80" class="rounded-lg shadow-md object-cover h-64 w-full">
                    <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=400&q=80" class="rounded-lg shadow-md object-cover h-64 w-full mt-8">
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section class="bg-white py-16">
            <div class="max-w-5xl mx-auto px-6">
                <h3 class="text-2xl font-bold text-center mb-12">Why Choose Us?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="text-center p-6 border rounded-xl hover:shadow-lg transition">
                        <i class="fa-solid fa-wifi text-3xl text-blue-600 mb-4"></i>
                        <h4 class="font-bold mb-2">High-Speed Wi-Fi</h4>
                        <p class="text-sm text-gray-500">Stay connected with our ultra-fast fiber optic internet in all rooms.</p>
                    </div>
                    <!-- Feature 2 -->
                    <div class="text-center p-6 border rounded-xl hover:shadow-lg transition">
                        <i class="fa-solid fa-utensils text-3xl text-blue-600 mb-4"></i>
                        <h4 class="font-bold mb-2">Gourmet Dining</h4>
                        <p class="text-sm text-gray-500">Experience world-class culinary delights prepared by our expert chefs.</p>
                    </div>
                    <!-- Feature 3 -->
                    <div class="text-center p-6 border rounded-xl hover:shadow-lg transition">
                        <i class="fa-solid fa-person-swimming text-3xl text-blue-600 mb-4"></i>
                        <h4 class="font-bold mb-2">Infinity Pool</h4>
                        <p class="text-sm text-gray-500">Relax and unwind in our luxurious outdoor heated swimming pool.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Call to Action -->
        <section class="py-16 text-center">
            <h3 class="text-2xl font-bold mb-6">Ready for your next getaway?</h3>
            <a href="index.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 transition">Book a Room Now</a>
        </section>
    </main>

    <?php include 'config/footer.php'; ?>
</body>
</html>