<?php
session_start();
require_once '../config/database.php';

// Check if job ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: browse.php');
    exit();
}

$job_id = (int)$_GET['id'];

// Fetch job details with company information
$sql = "
    SELECT jobs.*, 
           users.name as company_name, 
           users.company_logo,
           users.company_location,
           users.company_description
    FROM jobs 
    JOIN users ON jobs.employer_id = users.id 
    WHERE jobs.id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$job_id]);
$job = $stmt->fetch();

// If job not found, redirect to browse page
if (!$job) {
    header('Location: browse.php');
    exit();
}

// Helper function for time elapsed string
function time_elapsed_string($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return "Just now";
            }
            return $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
        }
        return $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
    }
    if ($diff->d < 7) {
        return $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
    }
    if ($diff->w < 4) {
        return $diff->w . " week" . ($diff->w > 1 ? "s" : "") . " ago";
    }
    return date('M j, Y', strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - Job Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="../index.php" class="text-2xl font-bold text-blue-600">JobBoard</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="browse.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Browse Jobs</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="../dashboard.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <a href="../auth/logout.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    <?php else: ?>
                        <a href="../login.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="../auth/register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 ml-3">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Job Details -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($job['title']); ?></h1>
                    <div class="flex items-center text-gray-600 space-x-4">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                            </svg>
                            <?php echo htmlspecialchars($job['company_name']); ?>
                        </span>
                        <span class="flex items-center">
                            <svg class="h-5 w-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <?php echo htmlspecialchars($job['location']); ?>
                        </span>
                        <span class="flex items-center">
                            <svg class="h-5 w-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            Posted <?php echo time_elapsed_string($job['created_at']); ?>
                        </span>
                    </div>
                </div>
                <?php if ($job['company_logo']): ?>
                    <img src="<?php echo htmlspecialchars($job['company_logo']); ?>" alt="<?php echo htmlspecialchars($job['company_name']); ?> logo" class="w-24 h-24 object-contain">
                <?php endif; ?>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <h2 class="text-xl font-semibold mb-4">Job Description</h2>
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                        </div>

                        <h2 class="text-xl font-semibold mt-8 mb-4">Requirements</h2>
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                        </div>
                    </div>

                    <div>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Job Details</h3>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Employment Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($job['employment_type']); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($job['category']); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Salary Range</dt>
                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($job['salary_range']); ?></dd>
                                </div>
                            </dl>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="../applications/apply.php?job_id=<?php echo $job['id']; ?>" 
                                   class="mt-6 w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Apply Now
                                </a>
                            <?php else: ?>
                                <a href="../login.php" 
                                   class="mt-6 w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Login to Apply
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="mt-6 bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">About <?php echo htmlspecialchars($job['company_name']); ?></h3>
                            <p class="text-sm text-gray-600">
                                <?php echo nl2br(htmlspecialchars($job['company_description'])); ?>
                            </p>
                            <p class="text-sm text-gray-600 mt-4">
                                <svg class="h-5 w-5 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                <?php echo htmlspecialchars($job['company_location']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
