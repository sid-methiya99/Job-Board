<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Job Portal</title>
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

    <!-- Hero Section -->
    <div class="bg-blue-900 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-center mb-4">About JobPortal</h1>
            <p class="text-xl text-center text-blue-200">Connecting talented professionals with amazing opportunities</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Mission Section -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Mission</h2>
            <p class="text-lg text-gray-600 leading-relaxed">
                At JobPortal, we're dedicated to transforming the way people find jobs and companies hire talent. 
                Our platform connects skilled professionals with leading companies, making the job search and 
                recruitment process more efficient and effective for everyone involved.
            </p>
        </div>

        <!-- Features Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Smart Job Matching</h3>
                <p class="text-gray-600">Our intelligent system matches candidates with the most relevant job opportunities.</p>
            </div>
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Large Network</h3>
                <p class="text-gray-600">Connect with thousands of companies and millions of job seekers worldwide.</p>
            </div>
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Secure Platform</h3>
                <p class="text-gray-600">Your data is protected with industry-standard security measures.</p>
            </div>
        </div>

        <!-- Team Section -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Team</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4"></div>
                    <h3 class="text-xl font-semibold">John Doe</h3>
                    <p class="text-gray-600">CEO & Founder</p>
                </div>
                <!-- Add more team members as needed -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html> 