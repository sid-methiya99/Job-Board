<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    header('Location: index.php');
    exit();
}

// Check if job ID is provided
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$job_id = $_GET['id'];
$error = '';
$success = '';

// Fetch job details
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];
    $requirements = $_POST['requirements'];

    try {
        $stmt = $pdo->prepare("
            UPDATE jobs 
            SET title = ?, description = ?, location = ?, salary = ?, requirements = ?
            WHERE id = ? AND employer_id = ?
        ");
        $stmt->execute([$title, $description, $location, $salary, $requirements, $job_id, $_SESSION['user_id']]);
        $success = "Job updated successfully!";
        
        // Refresh job data
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
        $stmt->execute([$job_id, $_SESSION['user_id']]);
        $job = $stmt->fetch();
    } catch(PDOException $e) {
        $error = "Error updating job. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - Job Portal</title>
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
                <a href="post-job.php" class="flex items-center px-6 py-3 bg-blue-900">
                    <i class="fas fa-plus-circle mr-3"></i>
                    Post New Job
                </a>
                <a href="applications.php" class="flex items-center px-6 py-3 hover:bg-blue-900">
                    <i class="fas fa-users mr-3"></i>
                    Applications
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
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-gray-700 text-3xl font-medium">Edit Job</h3>
                    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                                Job Title
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                   id="title" type="text" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="location">
                                Location
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                   id="location" type="text" name="location" value="<?php echo htmlspecialchars($job['location']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="salary">
                                Salary Range
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                   id="salary" type="text" name="salary" value="<?php echo htmlspecialchars($job['salary']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                                Job Description
                            </label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                      id="description" name="description" rows="6" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="requirements">
                                Requirements
                            </label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                      id="requirements" name="requirements" rows="6" required><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                        </div>

                        <div class="flex items-center justify-between">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                    type="submit">
                                Update Job
                            </button>
                            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 