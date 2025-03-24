<?php
session_start();
require_once 'config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $subject, $message]);
            $success = "Thank you for your message. We'll get back to you soon!";
        } catch(PDOException $e) {
            $error = "Sorry, there was an error sending your message. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex-shrink-0 flex items-center">
                        <i class="fas fa-briefcase text-blue-600 text-2xl mr-2"></i>
                        <span class="text-xl font-bold text-gray-800">JobPortal</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_type'] == 'employer'): ?>
                            <a href="companies/dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <?php else: ?>
                            <a href="users/dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="register.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contact Form Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Contact Us</h1>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-6">
                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="name">
                            Your Name
                        </label>
                        <input type="text" id="name" name="name" required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="email">
                            Email Address
                        </label>
                        <input type="email" id="email" name="email" required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="subject">
                            Subject
                        </label>
                        <input type="text" id="subject" name="subject" required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="message">
                            Message
                        </label>
                        <textarea id="message" name="message" rows="6" required
                                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>

            <!-- Contact Information -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-phone text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Phone</h3>
                    <p class="mt-2 text-gray-600">+91 9876543210</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-envelope text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Email</h3>
                    <p class="mt-2 text-gray-600">jobportal@gmail.com</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html> 