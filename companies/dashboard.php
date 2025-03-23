<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    header('Location: ../login.php');
    exit();
}

// Fetch company's posted jobs
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$posted_jobs = $stmt->fetchAll();

// Count total applications for each status
$stmt = $pdo->prepare("
    SELECT 
        status,
        COUNT(*) as count
    FROM applications 
    WHERE job_id IN (SELECT id FROM jobs WHERE employer_id = ?)
    GROUP BY status
");
$stmt->execute([$_SESSION['user_id']]);
$application_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get recent applications
$stmt = $pdo->prepare("
    SELECT 
        applications.*,
        jobs.title as job_title,
        users.name as applicant_name,
        users.email as applicant_email
    FROM applications 
    JOIN jobs ON applications.job_id = jobs.id
    JOIN users ON applications.user_id = users.id
    WHERE jobs.employer_id = ?
    ORDER BY applications.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <div class="container mx-auto px-6 py-8">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-gray-700 text-3xl font-medium">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h3>
                    <a href="post-job.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Post New Job
                    </a>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Active Jobs Card -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-bold"><?php echo count($posted_jobs); ?></p>
                                <p class="text-sm opacity-80">Active Jobs</p>
                            </div>
                            <div class="p-3 bg-blue-400 bg-opacity-30 rounded-full">
                                <i class="fas fa-briefcase text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-sm">
                            <a href="post-job.php" class="flex items-center hover:opacity-80">
                                <span>Post New Job</span>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Total Applications Card -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-bold"><?php echo array_sum($application_stats); ?></p>
                                <p class="text-sm opacity-80">Total Applications</p>
                            </div>
                            <div class="p-3 bg-green-400 bg-opacity-30 rounded-full">
                                <i class="fas fa-file-alt text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-sm">
                            <a href="applications.php" class="flex items-center hover:opacity-80">
                                <span>View All</span>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pending Reviews Card -->
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-bold"><?php echo isset($application_stats['pending']) ? $application_stats['pending'] : 0; ?></p>
                                <p class="text-sm opacity-80">Pending Reviews</p>
                            </div>
                            <div class="p-3 bg-yellow-400 bg-opacity-30 rounded-full">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-sm">
                            <a href="applications.php?status=pending" class="flex items-center hover:opacity-80">
                                <span>Review Applications</span>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Hired Card -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-bold"><?php echo isset($application_stats['accepted']) ? $application_stats['accepted'] : 0; ?></p>
                                <p class="text-sm opacity-80">Hired</p>
                            </div>
                            <div class="p-3 bg-purple-400 bg-opacity-30 rounded-full">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-sm">
                            <a href="applications.php?status=accepted" class="flex items-center hover:opacity-80">
                                <span>View Hired</span>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Jobs -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-xl font-semibold text-gray-800">Recent Job Postings</h4>
                        <a href="post-job.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Post New Job
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posted Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applications</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach (array_slice($posted_jobs, 0, 5) as $job): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($job['title']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($job['location']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="applications.php?job_id=<?php echo $job['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">
                                                View Applications
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="edit-job.php?id=<?php echo $job['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete-job.php?id=<?php echo $job['id']; ?>" 
                                               class="text-red-600 hover:text-red-900"
                                               onclick="return confirm('Are you sure you want to delete this job?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Applications -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-xl font-semibold text-gray-800">Recent Applications</h4>
                        <div class="flex gap-2">
                            <select class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="accepted">Accepted</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <a href="applications.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applied Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($recent_applications as $application): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($application['applicant_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($application['applicant_email']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($application['job_title']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($application['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $application['status'] == 'accepted' ? 'bg-green-100 text-green-800' : 
                                                    ($application['status'] == 'rejected' ? 'bg-red-100 text-red-800' : 
                                                    'bg-yellow-100 text-yellow-800'); ?>">
                                                <?php echo ucfirst($application['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="view-application.php?id=<?php echo $application['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 