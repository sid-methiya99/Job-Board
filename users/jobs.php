<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a jobseeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'jobseeker') {
    header('Location: index.php');
    exit();
}

// Handle search and filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';

// Build the query
$query = "
    SELECT 
        jobs.*,
        employers.name as company_name,
        CASE 
            WHEN applications.id IS NOT NULL THEN 1 
            ELSE 0 
        END as has_applied
    FROM jobs 
    JOIN users as employers ON jobs.employer_id = employers.id
    LEFT JOIN applications ON jobs.id = applications.job_id 
        AND applications.user_id = ?
    WHERE 1=1
";

$params = [$_SESSION['user_id']];

if ($search) {
    $query .= " AND (jobs.title LIKE ? OR jobs.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($location) {
    $query .= " AND jobs.location LIKE ?";
    $params[] = "%$location%";
}

$query .= " ORDER BY jobs.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - Job Portal</title>
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
                <a href="dashboard.php" class="flex items-center px-6 py-3 hover:bg-blue-900">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="jobs.php" class="flex items-center px-6 py-3 bg-blue-900">
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
                <h3 class="text-gray-700 text-3xl font-medium mb-6">Browse Jobs</h3>

                <!-- Search Form -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <form method="GET" action="" class="flex gap-4">
                        <div class="flex-1">
                            <input type="text" name="search" placeholder="Search jobs by title or description" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:border-blue-500">
                        </div>
                        <div class="flex-1">
                            <input type="text" name="location" placeholder="Location" 
                                   value="<?php echo htmlspecialchars($location); ?>"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:border-blue-500">
                        </div>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Jobs List -->
                <div class="grid gap-6">
                    <?php foreach ($jobs as $job): ?>
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-xl font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($job['title']); ?>
                                    </h4>
                                    <p class="text-gray-600 mt-1">
                                        <?php echo htmlspecialchars($job['company_name']); ?>
                                    </p>
                                    <div class="flex items-center text-gray-500 mt-2">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <?php echo htmlspecialchars($job['location']); ?>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-money-bill-alt mr-2"></i>
                                        <?php echo htmlspecialchars($job['salary']); ?>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($job['has_applied']): ?>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                            Applied
                                        </span>
                                    <?php else: ?>
                                        <a href="apply.php?job_id=<?php echo $job['id']; ?>" 
                                           class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                            Apply Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-4">
                                <h5 class="font-semibold text-gray-900 mb-2">Description:</h5>
                                <p class="text-gray-700">
                                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                                </p>
                            </div>
                            <div class="mt-4">
                                <h5 class="font-semibold text-gray-900 mb-2">Requirements:</h5>
                                <p class="text-gray-700">
                                    <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                                </p>
                            </div>
                            <div class="mt-4 text-gray-500 text-sm">
                                Posted on: <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 