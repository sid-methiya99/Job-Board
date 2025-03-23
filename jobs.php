<?php
session_start();
require_once 'config/database.php';

// Fetch all jobs from database
$stmt = $pdo->query("
    SELECT jobs.*, users.name as company_name 
    FROM jobs 
    JOIN users ON jobs.employer_id = users.id 
    ORDER BY jobs.created_at DESC
");
$jobs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Jobs - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold text-blue-600">JobPortal</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-blue-600">Login</a>
                        <a href="register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Available Jobs</h1>

        <!-- Jobs Grid -->
        <div class="grid gap-6">
            <?php foreach ($jobs as $job): ?>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($job['title']); ?>
                            </h2>
                            <p class="text-gray-600 mb-2">
                                <?php echo htmlspecialchars($job['company_name']); ?>
                            </p>
                            <p class="text-gray-500 mb-4">
                                <span class="mr-4">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($job['location']); ?>
                                </span>
                                <span>
                                    <i class="fas fa-money-bill-alt"></i> 
                                    <?php echo htmlspecialchars($job['salary']); ?>
                                </span>
                            </p>
                        </div>
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'jobseeker'): ?>
                            <a href="apply.php?job_id=<?php echo $job['id']; ?>" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Apply Now
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Description:</h3>
                        <p class="text-gray-700">
                            <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                        </p>
                    </div>
                    <div class="mt-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Requirements:</h3>
                        <p class="text-gray-700">
                            <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 