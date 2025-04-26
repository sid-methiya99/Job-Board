<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch statistics
try {
    // Total users count
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE user_type = 'jobseeker'");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Total companies count
    $stmt = $pdo->query("SELECT COUNT(*) as total_companies FROM users WHERE user_type = 'employer'");
    $totalCompanies = $stmt->fetch(PDO::FETCH_ASSOC)['total_companies'];

    // Total jobs count
    $stmt = $pdo->query("SELECT COUNT(*) as total_jobs FROM jobs");
    $totalJobs = $stmt->fetch(PDO::FETCH_ASSOC)['total_jobs'];

    // Total applications count
    $stmt = $pdo->query("SELECT COUNT(*) as total_applications FROM applications");
    $totalApplications = $stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];

    // Recent Activities (last 5 activities)
    $recentActivities = [];
    
    // Recent job postings
    $stmt = $pdo->query("SELECT j.title, u.name as company, j.created_at 
                        FROM jobs j 
                        JOIN users u ON j.employer_id = u.id 
                        ORDER BY j.created_at DESC LIMIT 5");
    $recentJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent applications
    $stmt = $pdo->query("SELECT a.id, j.title, u.name as applicant, a.created_at, a.status
                        FROM applications a
                        JOIN jobs j ON a.job_id = j.id
                        JOIN users u ON a.user_id = u.id
                        ORDER BY a.created_at DESC LIMIT 5");
    $recentApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Get admin info
$adminName = $_SESSION['admin_username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-800 text-white p-6">
            <div class="mb-8">
                <h1 class="text-2xl font-bold">Job Portal</h1>
                <p class="text-sm text-blue-200">Admin Dashboard</p>
            </div>
            <nav class="space-y-4">
                <a href="index.php" class="block py-2.5 px-4 rounded bg-blue-900">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="users.php" class="block py-2.5 px-4 rounded hover:bg-blue-700">
                    <i class="fas fa-users mr-2"></i>Users
                </a>
                <a href="companies.php" class="block py-2.5 px-4 rounded hover:bg-blue-700">
                    <i class="fas fa-building mr-2"></i>Companies
                </a>
                <a href="jobs.php" class="block py-2.5 px-4 rounded hover:bg-blue-700">
                    <i class="fas fa-briefcase mr-2"></i>Jobs
                </a>
                <a href="applications.php" class="block py-2.5 px-4 rounded hover:bg-blue-700">
                    <i class="fas fa-file-alt mr-2"></i>Applications
                </a>
                <a href="logout.php" class="block py-2.5 px-4 rounded hover:bg-blue-700 mt-8">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="mb-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-800">Welcome back, <?php echo htmlspecialchars($adminName); ?>!</h1>
                <div class="text-sm text-gray-600"><?php echo date('l, F j, Y'); ?></div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-user text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm font-medium">Total Users</h3>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $totalUsers; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-building text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm font-medium">Total Companies</h3>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $totalCompanies; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-briefcase text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm font-medium">Total Jobs</h3>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $totalJobs; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-file-alt text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm font-medium">Applications</h3>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $totalApplications; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Recent Jobs -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-xl font-bold mb-4 text-gray-800">Recent Job Postings</h2>
                    <div class="space-y-4">
                        <?php foreach ($recentJobs as $job): ?>
                        <div class="border-b border-gray-100 pb-3">
                            <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($job['title']); ?></h3>
                            <p class="text-sm text-gray-600">
                                <span class="text-blue-600"><?php echo htmlspecialchars($job['company']); ?></span> • 
                                <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="jobs.php" class="mt-4 inline-block text-blue-600 hover:text-blue-800 text-sm">View all jobs →</a>
                </div>
                
                <!-- Recent Applications -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-xl font-bold mb-4 text-gray-800">Recent Applications</h2>
                    <div class="space-y-4">
                        <?php foreach ($recentApplications as $application): ?>
                        <div class="border-b border-gray-100 pb-3">
                            <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($application['title']); ?></h3>
                            <p class="text-sm text-gray-600">
                                <span class="text-blue-600"><?php echo htmlspecialchars($application['applicant']); ?></span> • 
                                <?php 
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'reviewed' => 'bg-blue-100 text-blue-800',
                                        'accepted' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusColor = $statusColors[$application['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-block px-2 py-1 rounded-full text-xs <?php echo $statusColor; ?>">
                                    <?php echo ucfirst($application['status']); ?>
                                </span>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="applications.php" class="mt-4 inline-block text-blue-600 hover:text-blue-800 text-sm">View all applications →</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Add active state to current nav item
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('nav a');
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath.split('/').pop()) {
                    link.classList.add('bg-blue-900');
                }
            });
        });
    </script>
</body>
</html> 