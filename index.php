<?php
session_start();
require_once 'config/database.php';

// Fetch recent jobs
$stmt = $pdo->prepare("
    SELECT jobs.*, users.name as company_name, users.company_logo 
    FROM jobs 
    JOIN users ON jobs.employer_id = users.id 
    ORDER BY created_at DESC 
    LIMIT 6
");
$stmt->execute();
$recent_jobs = $stmt->fetchAll();

// Fetch featured companies
$stmt = $pdo->prepare("
    SELECT DISTINCT users.*, 
           COUNT(jobs.id) as job_count 
    FROM users 
    JOIN jobs ON users.id = jobs.employer_id 
    WHERE users.user_type = 'employer' 
    GROUP BY users.id 
    LIMIT 4
");
$stmt->execute();
$featured_companies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal - Find Your Dream Job</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-pattern {
            background-color: #1a365d;
            background-image: radial-gradient(at 50% 100%, rgba(56, 189, 248, 0.25) 0%, rgba(29, 78, 216, 0.25) 50%, transparent 100%);
        }
    </style>
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
    <div class="hero-pattern">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-white sm:text-5xl md:text-6xl">
                    Find Your Dream Job Today
                </h1>
                <p class="mt-3 max-w-md mx-auto text-base text-gray-300 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                    Connect with top companies and opportunities that match your skills and aspirations.
                </p>
                <div class="mt-10">
                    <form action="jobs/search.php" method="GET" class="max-w-3xl mx-auto flex gap-4">
                        <input type="text" name="q" placeholder="Job title, keywords, or company" 
                               class="flex-1 rounded-lg border-0 px-4 py-3 text-gray-900 shadow-sm focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                            Search Jobs
                        </button>
                    </form>
                </div>
                <div class="mt-8 flex justify-center space-x-4">
                    <a href="register.php?type=jobseeker" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-user-tie mr-2"></i>
                        Find Jobs
                    </a>
                    <a href="register.php?type=employer" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        <i class="fas fa-building mr-2"></i>
                        Post Jobs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Jobs Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Recent Job Openings</h2>
            <a href="jobs/browse.php" class="text-blue-600 hover:text-blue-800 font-semibold">View All Jobs</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($recent_jobs as $job): ?>
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-start justify-between">
                    <div class="flex items-center">
                        <?php if (!empty($job['company_logo'])): ?>
                            <img src="uploads/logos/<?php echo htmlspecialchars($job['company_logo']); ?>" 
                                 alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                 class="w-12 h-12 object-contain rounded">
                        <?php else: ?>
                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                <i class="fas fa-building text-gray-400 text-2xl"></i>
                            </div>
                        <?php endif; ?>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($job['company_name']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-gray-500 text-sm mb-2">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        <?php echo htmlspecialchars($job['location']); ?>
                    </div>
                    <div class="flex items-center text-gray-500 text-sm">
                        <i class="fas fa-money-bill-alt mr-2"></i>
                        <?php echo htmlspecialchars($job['salary']); ?>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="jobs/view.php?id=<?php echo $job['id']; ?>" 
                       class="block w-full text-center bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg font-semibold">
                        View Details
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Featured Companies -->
    <div class="bg-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Featured Companies</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($featured_companies as $company): ?>
                <a href="companies/public-profile.php?id=<?php echo $company['id']; ?>" 
                   class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex items-center justify-center mb-4">
                        <?php if (!empty($company['company_logo'])): ?>
                            <img src="uploads/logos/<?php echo htmlspecialchars($company['company_logo']); ?>" 
                                 alt="<?php echo htmlspecialchars($company['name']); ?>" 
                                 class="w-16 h-16 object-contain">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-building text-gray-400 text-2xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($company['name']); ?></h3>
                        <p class="text-blue-600 mt-2"><?php echo $company['job_count']; ?> open positions</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Why Choose Us -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Why Choose JobPortal</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Easy Job Search</h3>
                <p class="text-gray-600">Find the perfect job matching your skills and experience level.</p>
            </div>
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-building text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Top Companies</h3>
                <p class="text-gray-600">Connect with leading companies and exciting startups.</p>
            </div>
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-rocket text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Career Growth</h3>
                <p class="text-gray-600">Take your career to the next level with great opportunities.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">About JobPortal</h3>
                    <p class="text-gray-400">Connecting talented professionals with amazing opportunities.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="jobs/browse.php" class="text-gray-400 hover:text-white">Browse Jobs</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">For Employers</h3>
                    <ul class="space-y-2">
                        <li><a href="register.php?type=employer" class="text-gray-400 hover:text-white">Post a Job</a></li>
                        <li><a href="pricing.php" class="text-gray-400 hover:text-white">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Connect With Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> JobPortal. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
