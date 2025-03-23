<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    header('Location: ../login.php');
    exit();
}

// Handle application status updates
if (isset($_POST['application_id']) && isset($_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ? AND job_id IN (SELECT id FROM jobs WHERE employer_id = ?)");
    $stmt->execute([$_POST['status'], $_POST['application_id'], $_SESSION['user_id']]);
}

// Fetch all applications for the company's jobs
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
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - Job Portal</title>
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
                <h3 class="text-gray-700 text-3xl font-medium mb-6">Job Applications</h3>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Job Title
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Applicant
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Applied Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($applications as $application): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($application['job_title']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($application['applicant_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($application['applicant_email']); ?>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" action="" class="inline-block">
                                            <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" 
                                                    class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <option value="pending" <?php echo $application['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="reviewed" <?php echo $application['status'] == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                                <option value="accepted" <?php echo $application['status'] == 'accepted' ? 'selected' : ''; ?>>Accept</option>
                                                <option value="rejected" <?php echo $application['status'] == 'rejected' ? 'selected' : ''; ?>>Reject</option>
                                            </select>
                                        </form>
                                        <a href="view-application.php?id=<?php echo $application['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 ml-3">
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
</body>
</html> 