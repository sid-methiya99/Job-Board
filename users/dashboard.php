<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a jobseeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'jobseeker') {
    header('Location: index.php');
    exit();
}

// Fetch user's applications
$stmt = $pdo->prepare("
    SELECT 
        applications.*,
        jobs.title,
        jobs.location,
        employers.name as company_name
    FROM applications 
    JOIN jobs ON applications.job_id = jobs.id
    JOIN users as employers ON jobs.employer_id = employers.id
    WHERE applications.user_id = ?
    ORDER BY applications.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();

// Fetch recommended jobs
$stmt = $pdo->query("
    SELECT 
        jobs.*,
        employers.name as company_name 
    FROM jobs 
    JOIN users as employers ON jobs.employer_id = employers.id
    ORDER BY created_at DESC 
    LIMIT 5
");
$recommended_jobs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Dashboard - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-blue-800 text-white w-64 py-6 flex flex-col">
            <div class="px-6 mb-8">
                <h2 class="text-2xl font-bold">JobPortal</h2>
            </div>
            <nav class="flex-1">
                <a href="dashboard.php" class="flex items-center px-6 py-3 bg-blue-900">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="jobs.php" class="flex items-center px-6 py-3 hover:bg-blue-900">
                    <i class="fas fa-briefcase mr-3"></i>
                    Browse Jobs
                </a>
                <a href="applications.php" class="flex items-center px-6 py-3 hover:bg-blue-900">
                    <i class="fas fa-file-alt mr-3"></i>
                    My Applications
                </a>
                <a href="profile.php" class="flex items-center px-6 py-3 hover:bg-blue-900">
                    <i class="fas fa-user mr-3"></i>
                    Profile
                </a>
            </nav>
            <div class="px-6 py-4">
                <a href="logout.php" class="flex items-center text-white hover:text-gray-200">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <div class="container mx-auto px-6 py-8">
                <h3 class="text-gray-700 text-3xl font-medium mb-4">
                    Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
                </h3>

                <!-- Stats -->
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-600 bg-opacity-75">
                                <i class="fas fa-file-alt text-white text-2xl"></i>
                            </div>
                            <div class="mx-5">
                                <h4 class="text-2xl font-semibold text-gray-700">
                                    <?php echo count($applications); ?>
                                </h4>
                                <div class="text-gray-500">Applications</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Applications -->
                <div class="mt-8">
                    <h4 class="text-gray-700 text-lg font-medium mb-4">Recent Applications</h4>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($applications as $application): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($application['title']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($application['company_name']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $application['status'] == 'accepted' ? 'bg-green-100 text-green-800' : 
                                                    ($application['status'] == 'rejected' ? 'bg-red-100 text-red-800' : 
                                                    'bg-yellow-100 text-yellow-800'); ?>">
                                                <?php echo ucfirst($application['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($application['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recommended Jobs -->
                <div class="mt-8">
                    <h4 class="text-gray-700 text-lg font-medium mb-4">Recommended Jobs</h4>
                    <div class="grid gap-6">
                        <?php foreach ($recommended_jobs as $job): ?>
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h5 class="text-xl font-semibold text-gray-700">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </h5>
                                        <p class="text-gray-600 mt-2">
                                            <?php echo htmlspecialchars($job['company_name']); ?>
                                        </p>
                                        <p class="text-gray-500 mt-2">
                                            <i class="fas fa-map-marker-alt mr-2"></i>
                                            <?php echo htmlspecialchars($job['location']); ?>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-money-bill-alt mr-2"></i>
                                            <?php echo htmlspecialchars($job['salary']); ?>
                                        </p>
                                    </div>
                                    <a href="apply.php?job_id=<?php echo $job['id']; ?>" 
                                       class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                        Apply Now
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 