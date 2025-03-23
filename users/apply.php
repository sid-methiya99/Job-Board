<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a jobseeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'jobseeker') {
    header('Location: index.php');
    exit();
}

// Check if job_id is provided
if (!isset($_GET['job_id'])) {
    header('Location: jobs.php');
    exit();
}

$job_id = $_GET['job_id'];

// Fetch job details
$stmt = $pdo->prepare("
    SELECT jobs.*, employers.name as company_name
    FROM jobs 
    JOIN users as employers ON jobs.employer_id = employers.id
    WHERE jobs.id = ?
");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: jobs.php');
    exit();
}

// Check if already applied
$stmt = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND user_id = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    header('Location: jobs.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['resume']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            // Create uploads directory if it doesn't exist
            if (!file_exists('../uploads')) {
                mkdir('../uploads', 0777, true);
            }
            
            // Generate unique filename
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = '../uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_path)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO applications (job_id, user_id, resume, cover_letter) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $job_id,
                        $_SESSION['user_id'],
                        $new_filename,
                        $_POST['cover_letter']
                    ]);
                    $success = "Application submitted successfully!";
                } catch(PDOException $e) {
                    $error = "Error submitting application. Please try again.";
                }
            } else {
                $error = "Error uploading file. Please try again.";
            }
        } else {
            $error = "Invalid file type. Please upload PDF or Word document.";
        }
    } else {
        $error = "Please upload your resume.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job - Job Portal</title>
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
                <h3 class="text-gray-700 text-3xl font-medium mb-6">Apply for Job</h3>

                <!-- Job Details -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h4 class="text-xl font-semibold text-gray-900 mb-2">
                        <?php echo htmlspecialchars($job['title']); ?>
                    </h4>
                    <p class="text-gray-600">
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

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success; ?>
                        <p class="mt-2">
                            <a href="jobs.php" class="text-green-700 underline">Return to jobs listing</a>
                        </p>
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Application Form -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-6">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="resume">
                                    Upload Resume (PDF, DOC, DOCX)
                                </label>
                                <input type="file" name="resume" id="resume" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                                       accept=".pdf,.doc,.docx" required>
                            </div>

                            <div class="mb-6">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="cover_letter">
                                    Cover Letter
                                </label>
                                <textarea name="cover_letter" id="cover_letter" rows="6"
                                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                                          placeholder="Write a brief cover letter..."></textarea>
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="submit" 
                                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                    Submit Application
                                </button>
                                <a href="jobs.php" class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 