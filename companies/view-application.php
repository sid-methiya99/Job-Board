<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    header('Location: index.php');
    exit();
}

// Check if application ID is provided
if (!isset($_GET['id'])) {
    header('Location: applications.php');
    exit();
}

$application_id = $_GET['id'];

// Fetch application details with job and applicant information
$stmt = $pdo->prepare("
    SELECT 
        applications.*,
        jobs.title as job_title,
        jobs.location,
        jobs.salary,
        users.name as applicant_name,
        users.email as applicant_email,
        users.linkedin_url,
        users.github_url,
        users.experience,
        users.skills,
        users.bio
    FROM applications 
    JOIN jobs ON applications.job_id = jobs.id
    JOIN users ON applications.user_id = users.id
    WHERE applications.id = ? 
    AND jobs.employer_id = ?
");
$stmt->execute([$application_id, $_SESSION['user_id']]);
$application = $stmt->fetch();

// If application not found or doesn't belong to this employer
if (!$application) {
    header('Location: applications.php');
    exit();
}

// Add a success message variable
$status_message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    
    try {
        // Update application status
        $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $application_id]);
        
        // Set success message based on status
        $status_message = $new_status == 'accepted' 
            ? "Application has been accepted! The candidate will be notified."
            : ($new_status == 'rejected' 
                ? "Application has been rejected. The candidate will be notified."
                : "Application status has been updated.");

        $_SESSION['status_message'] = $status_message;
        header("Location: view-application.php?id=" . $application_id);
        exit();
        
    } catch(PDOException $e) {
        $error = "Error updating application status.";
    }
}

// Get message from session
$status_message = isset($_SESSION['status_message']) ? $_SESSION['status_message'] : '';
unset($_SESSION['status_message']); // Clear after use
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .popup {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        }

        .popup.success {
            background-color: #34D399;
            color: white;
        }

        .popup.error {
            background-color: #EF4444;
            color: white;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Add popup div -->
    <div id="statusPopup" class="popup">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="popupMessage"></span>
        </div>
    </div>

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
                <a href="post-job.php" class="flex items-center px-6 py-3 hover:bg-blue-900">
                    <i class="fas fa-plus-circle mr-3"></i>
                    Post New Job
                </a>
                <a href="applications.php" class="flex items-center px-6 py-3 bg-blue-900">
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
                    <h3 class="text-gray-700 text-3xl font-medium">Application Details</h3>
                    <a href="applications.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Applications
                    </a>
                </div>

                <!-- Application Status Card -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($application['job_title']); ?>
                            </h4>
                            <p class="text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?php echo htmlspecialchars($application['location']); ?>
                                <span class="mx-2">•</span>
                                <i class="fas fa-money-bill-alt mr-2"></i>
                                <?php echo htmlspecialchars($application['salary']); ?>
                            </p>
                        </div>
                        <form method="POST" action="" class="flex items-center">
                            <select name="status" onchange="this.form.submit()" 
                                    class="bg-white border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending" <?php echo $application['status'] == 'pending' ? 'selected' : ''; ?>>
                                    Pending
                                </option>
                                <option value="reviewed" <?php echo $application['status'] == 'reviewed' ? 'selected' : ''; ?>>
                                    Reviewed
                                </option>
                                <option value="accepted" <?php echo $application['status'] == 'accepted' ? 'selected' : ''; ?>>
                                    Accept
                                </option>
                                <option value="rejected" <?php echo $application['status'] == 'rejected' ? 'selected' : ''; ?>>
                                    Reject
                                </option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Applicant Details -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Applicant Information</h4>
                    
                    <!-- Basic Info -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-gray-600 mb-2">Name</p>
                            <p class="font-medium"><?php echo htmlspecialchars($application['applicant_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600 mb-2">Email</p>
                            <p class="font-medium"><?php echo htmlspecialchars($application['applicant_email']); ?></p>
                        </div>
                    </div>

                    <!-- Professional Links -->
                    <?php if (!empty($application['linkedin_url']) || !empty($application['github_url'])): ?>
                    <div class="mb-6">
                        <p class="text-gray-600 mb-3">Professional Links</p>
                        <div class="flex space-x-4">
                            <?php if (!empty($application['linkedin_url'])): ?>
                            <a href="<?php echo htmlspecialchars($application['linkedin_url']); ?>" 
                               target="_blank"
                               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                <i class="fab fa-linkedin mr-2"></i>
                                LinkedIn Profile
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($application['github_url'])): ?>
                            <a href="<?php echo htmlspecialchars($application['github_url']); ?>" 
                               target="_blank"
                               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                <i class="fab fa-github mr-2"></i>
                                GitHub Profile
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Skills -->
                    <?php if (!empty($application['skills'])): ?>
                    <div class="mb-6">
                        <p class="text-gray-600 mb-3">Skills</p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach (explode(',', $application['skills']) as $skill): ?>
                                <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
                                    <?php echo htmlspecialchars(trim($skill)); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Experience -->
                    <?php if (!empty($application['experience'])): ?>
                    <div class="mb-6">
                        <p class="text-gray-600 mb-3">Work Experience</p>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?php echo nl2br(htmlspecialchars($application['experience'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Bio -->
                    <?php if (!empty($application['bio'])): ?>
                    <div class="mb-6">
                        <p class="text-gray-600 mb-3">About</p>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?php echo nl2br(htmlspecialchars($application['bio'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Application Details -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 mb-2">Applied On</p>
                                <p class="font-medium"><?php echo date('F d, Y', strtotime($application['created_at'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 mb-2">Status</p>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $application['status'] == 'accepted' ? 'bg-green-100 text-green-800' : 
                                        ($application['status'] == 'rejected' ? 'bg-red-100 text-red-800' : 
                                        'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo ucfirst($application['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Documents -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-4">Documents</h4>
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-600 mb-2">Resume</p>
                            <a href="../uploads/<?php echo $application['resume']; ?>" 
                               target="_blank"
                               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                <i class="fas fa-file-pdf mr-2"></i>
                                View Resume
                            </a>
                        </div>
                        <?php if ($application['cover_letter']): ?>
                            <div>
                                <p class="text-gray-600 mb-2">Cover Letter</p>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showPopup(message, type = 'success') {
            const popup = document.getElementById('statusPopup');
            const popupMessage = document.getElementById('popupMessage');
            
            popup.className = 'popup ' + type;
            popupMessage.textContent = message;
            popup.style.display = 'block';

            // Hide popup after 3 seconds
            setTimeout(() => {
                popup.style.animation = 'fadeOut 0.5s ease-out';
                setTimeout(() => {
                    popup.style.display = 'none';
                    popup.style.animation = '';
                }, 500);
            }, 3000);
        }

        // Show popup if there's a message
        <?php if ($status_message): ?>
            showPopup(<?php echo json_encode($status_message); ?>);
        <?php endif; ?>

        // Update the form submission to show popup
        document.querySelector('form').addEventListener('submit', function() {
            const status = document.querySelector('select[name="status"]').value;
            let message = '';
            
            switch(status) {
                case 'accepted':
                    message = 'Application has been accepted!';
                    break;
                case 'rejected':
                    message = 'Application has been rejected.';
                    break;
                default:
                    message = 'Application status has been updated.';
            }
            
            showPopup(message);
        });
    </script>
</body>
</html> 