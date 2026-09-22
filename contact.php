<?php
session_start();
include 'config/db.php';

$message_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $user_msg = trim($_POST['message']);

    // Ensure target table exists
    $conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(100),
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    if (!empty($name) && !empty($email) && !empty($user_msg)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $user_msg);
        
        if ($stmt->execute()) {
            $message_status = "<div class='bg-green-50 text-green-700 p-3 rounded text-sm border border-green-100'>Thank you! Your message has been sent successfully.</div>";
        } else {
            $message_status = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm border border-red-100'>Error sending message. Please try again later.</div>";
        }
        $stmt->close();
    } else {
        $message_status = "<div class='bg-red-50 text-red-700 p-3 rounded text-sm border border-red-100'>All form fields are required.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Trio's Villa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <div>
        <?php include 'config/navbar.php'; ?>

        <!-- Hero Title Layout -->
        <header class="relative bg-cover bg-center h-[200px] flex items-center justify-center text-center text-white" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80');">
            <div>
                <h1 class="text-3xl font-extrabold mb-1">Contact Us</h1>
                <p class="text-xs font-light text-gray-200 uppercase tracking-wider">Get in touch with our team</p>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="max-w-3xl mx-auto px-6 py-12">
            <!-- Contact Input Panel Form Workspace -->
            <div class="bg-white p-8 rounded-xl border border-gray-100 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Send an Inquiry</h2>
                    <p class="text-xs text-gray-400 mt-0.5">We typically respond to requests within 24 operational hours.</p>
                </div>

                <?php echo $message_status; ?>

                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Your Name</label>
                            <input type="text" name="name" required placeholder="John Doe" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Email Address</label>
                            <input type="email" name="email" required placeholder="john@example.com" class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase">Message Body</label>
                        <textarea name="message" rows="5" required placeholder="Write your question or feedback details here..." class="w-full mt-1 p-2 border text-sm rounded focus:outline-blue-500 resize-none"></textarea>
                    </div>
                    <button type="submit" name="send_message" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium px-8 py-2 rounded text-sm transition">
                        Send Message
                    </button>
                </form>
            </div>
        </main>
    </div>
<section class="max-w-6xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-bold text-center mb-12">Visit Us</h2>
    
    <!-- This div needs to wrap both the map and the details to form the grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        
        <!-- Google Map Embed -->
        <div class="w-full h-80 rounded-lg overflow-hidden shadow-lg">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3768.9950434910043!2d72.88686597373871!3d19.151694249594755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7b798ba77e969%3A0x98cb7efba86330e0!2sRoyal%20Palms%20Villas%20with%20Private%20Pool!5e0!3m2!1sen!2sin!4v1784469779170!5m2!1sen!2sin" 
            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>

        <!-- Contact Details -->
        <div class="flex flex-col">
            <h3 class="text-2xl font-bold mb-4">Get in Touch</h3>
            <p class="text-gray-600 mb-6">We'd love to hear from you. Feel free to reach out with any questions about your stay.</p>
            
            <ul class="space-y-4">
                <li class="flex items-center text-gray-700">
                    <i class="fas fa-map-marker-alt text-amber-500 mr-4 text-xl"></i>
                    123 Luxury Lane, Villa City
                </li>
                <li class="flex items-center text-gray-700">
                    <i class="fas fa-envelope text-amber-500 mr-4 text-xl"></i>
                    info@triosvilla.com
                </li>
                <li class="flex items-center text-gray-700">
                    <i class="fas fa-phone text-amber-500 mr-4 text-xl"></i>
                    +91 123 456 7890
                </li>
            </ul>
        </div>
    </div>
</section>
    <?php include 'config/footer.php'; ?>
</body>
</html>