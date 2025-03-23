<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a jobseeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'jobseeker') {
    header('Location: index.php');
    exit();
}

// Fetch user's applications with job and company details
$stmt = $pdo->prepare("
    SELECT 
        applications.*,
        jobs.title as job_title,
        jobs.location,
        jobs.salary,
        employers.name as company_name
    FROM applications 
    JOIN jobs ON applications.job_id = jobs.id
    JOIN users as employers ON jobs.employer_id = employers.id
    WHERE applications.user_id = ?
    ORDER BY applications.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Job Portal</title>
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
                <a href="jobs.php" class="flex items-center px-6 py-3 hover:bg-blue-900">
                    <i class="fas fa-briefcase mr-3"></i>
                    Browse Jobs
                </a>
                <a href="applications.php" class="flex items-center px-6 py-3 bg-blue-900">
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
                <h3 class="text-gray-700 text-3xl font-medium mb-6">My Applications</h3>

                <?php if (empty($applications)): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <p class="text-gray-600">You haven't applied to any jobs yet.</p>
                        <a href="jobs.php" class="inline-block mt-4 text-blue-600 hover:text-blue-800">
                            Browse Available Jobs
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Job Details
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Company
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Applied Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Documents
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($applications as $application): ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($application['job_title']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?php echo htmlspecialchars($application['location']); ?>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-money-bill-alt mr-1"></i>
                                                <?php echo htmlspecialchars($application['salary']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                <?php echo htmlspecialchars($application['company_name']); ?>
                                            </div>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="../uploads/<?php echo $application['resume']; ?>" 
                                               target="_blank"
                                               class="text-blue-600 hover:text-blue-900">
                                                View Resume
                                            </a>
                                            <?php if ($application['cover_letter']): ?>
                                                <span class="mx-2">•</span>
                                                <button onclick="showCoverLetter('<?php echo htmlspecialchars($application['cover_letter']); ?>')"
                                                        class="text-blue-600 hover:text-blue-900">
                                                    View Cover Letter
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cover Letter Modal -->
    <div id="coverLetterModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-2">Cover Letter</h3>
                <div class="mt-2 px-7 py-3">
                    <p id="coverLetterText" class="text-sm text-gray-500"></p>
                </div>
                <div class="mt-4 text-right">
                    <button onclick="hideCoverLetter()"
                            class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showCoverLetter(coverLetter) {
            document.getElementById('coverLetterText').textContent = coverLetter;
            document.getElementById('coverLetterModal').classList.remove('hidden');
        }

        function hideCoverLetter() {
            document.getElementById('coverLetterModal').classList.add('hidden');
        }
    </script>
</body>
</html> 