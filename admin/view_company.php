<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get company ID from URL
$company_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$company_id) {
    header('Location: companies.php');
    exit();
}

// Fetch company details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'employer'");
$stmt->execute([$company_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    header('Location: companies.php');
    exit();
}

// Fetch company's jobs
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
$stmt->execute([$company_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch company's applications
$stmt = $pdo->prepare("SELECT a.*, j.title as job_title, u.name as applicant_name 
                      FROM applications a 
                      JOIN jobs j ON a.job_id = j.id 
                      JOIN users u ON a.user_id = u.id 
                      WHERE j.employer_id = ? 
                      ORDER BY a.created_at DESC");
$stmt->execute([$company_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Company - Admin Dashboard</title>
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
                <a href="index.php" class="block py-2.5 px-4 rounded hover:bg-blue-700">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="users.php" class="block py-2.5 px-4 rounded hover:bg-blue-700">
                    <i class="fas fa-users mr-2"></i>Users
                </a>
                <a href="companies.php" class="block py-2.5 px-4 rounded bg-blue-900">
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
                <h1 class="text-3xl font-bold text-gray-800">Company Details</h1>
                <div class="flex items-center space-x-4">
                    <a href="edit_company.php?id=<?php echo $company_id; ?>" class="text-yellow-600 hover:text-yellow-800">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <a href="delete_company.php?id=<?php echo $company_id; ?>" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </a>
                    <a href="companies.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Companies
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Company Information -->
                <div class="col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center space-x-4 mb-6">
                            <?php if ($company['company_logo']): ?>
                            <img src="<?php echo htmlspecialchars($company['company_logo']); ?>" alt="Company Logo" 
                                 class="h-16 w-16 rounded-full object-cover">
                            <?php else: ?>
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-building text-gray-500 text-2xl"></i>
                            </div>
                            <?php endif; ?>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($company['name']); ?></h2>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($company['email']); ?></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Company Website</h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    <?php if ($company['company_website']): ?>
                                    <a href="<?php echo htmlspecialchars($company['company_website']); ?>" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <?php echo htmlspecialchars($company['company_website']); ?>
                                    </a>
                                    <?php else: ?>
                                    <span class="text-gray-500">Not provided</span>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Company Size</h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($company['company_size'] ?? 'Not provided'); ?>
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Industry</h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($company['company_industry'] ?? 'Not provided'); ?>
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Location</h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($company['company_location'] ?? 'Not provided'); ?>
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Member Since</h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    <?php echo date('F j, Y', strtotime($company['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jobs and Applications -->
                <div class="col-span-2 space-y-8">
                    <!-- Active Jobs -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Active Jobs</h2>
                        <?php if ($jobs): ?>
                        <div class="space-y-4">
                            <?php foreach ($jobs as $job): ?>
                            <div class="border-b border-gray-200 pb-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h3>
                                        <p class="text-sm text-gray-500">Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?></p>
                                    </div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-500">No active jobs found.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Applications -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Applications</h2>
                        <?php if ($applications): ?>
                        <div class="space-y-4">
                            <?php foreach ($applications as $application): ?>
                            <div class="border-b border-gray-200 pb-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($application['applicant_name']); ?></h3>
                                        <p class="text-sm text-gray-500">Applied for <?php echo htmlspecialchars($application['job_title']); ?></p>
                                    </div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $application['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                      ($application['status'] === 'reviewed' ? 'bg-blue-100 text-blue-800' : 
                                                      ($application['status'] === 'accepted' ? 'bg-green-100 text-green-800' : 
                                                      'bg-red-100 text-red-800')); ?>">
                                        <?php echo ucfirst($application['status']); ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    Applied <?php echo date('M j, Y', strtotime($application['created_at'])); ?>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-500">No applications found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 