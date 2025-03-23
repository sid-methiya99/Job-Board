<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a jobseeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'jobseeker') {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $linkedin_url = $_POST['linkedin_url'] ?? null;
    $github_url = $_POST['github_url'] ?? null;
    $experience = $_POST['experience'] ?? null;
    $skills = $_POST['skills'] ?? null;
    $bio = $_POST['bio'] ?? null;
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    try {
        // Start with basic update SQL
        $sql = "UPDATE users SET 
                name = ?, 
                email = ?, 
                linkedin_url = ?,
                github_url = ?,
                experience = ?,
                skills = ?,
                bio = ?";
        
        $params = [$name, $email, $linkedin_url, $github_url, $experience, $skills, $bio];

        // Add password update if provided
        if ($current_password && $new_password) {
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
            $sql .= ", password = ?";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // Add WHERE clause
        $sql .= " WHERE id = ?";
        $params[] = $_SESSION['user_id'];

        // Execute update
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Set success message and refresh user data
        $_SESSION['success_message'] = "Profile updated successfully!";
        
        // Refresh the page to show updated data
        header("Location: profile.php");
        exit();

    } catch(Exception $e) {
        $error = $e->getMessage();
        // For debugging:
        error_log("Profile update error: " . $e->getMessage());
    }
}

// Get success message from session
$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']); // Clear the message after getting it

// Add this at the top of the file to show PHP errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Job Portal</title>
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
                <a href="jobs.php" class="flex items-center px-6 py-3 hover:bg-blue-900">
                    <i class="fas fa-briefcase mr-3"></i>
                    Browse Jobs
                </a>
                <a href="applications.php" class="flex items-center px-6 py-3 hover:bg-blue-900">
                    <i class="fas fa-file-alt mr-3"></i>
                    My Applications
                </a>
                <a href="profile.php" class="flex items-center px-6 py-3 bg-blue-900">
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
                <h3 class="text-gray-700 text-3xl font-medium mb-6">Profile Settings</h3>

                <!-- Profile Form -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <form method="POST" action="">
                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">Basic Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                                        Full Name
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           id="name" type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>

                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                        Email
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           id="email" type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">Professional Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="linkedin_url">
                                        LinkedIn Profile URL
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           id="linkedin_url" type="url" name="linkedin_url" value="<?php echo htmlspecialchars($user['linkedin_url'] ?? ''); ?>">
                                </div>

                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="github_url">
                                        GitHub Profile URL
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           id="github_url" type="url" name="github_url" value="<?php echo htmlspecialchars($user['github_url'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Skills and Experience -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">Skills and Experience</h4>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="skills">
                                    Skills (separate with commas)
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                       id="skills" type="text" name="skills" value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>"
                                       placeholder="e.g., PHP, JavaScript, React, Node.js">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="experience">
                                    Work Experience
                                </label>
                                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                          id="experience" name="experience" rows="4"
                                          placeholder="Brief description of your work experience"><?php echo htmlspecialchars($user['experience'] ?? ''); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="bio">
                                    Bio
                                </label>
                                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                          id="bio" name="bio" rows="4"
                                          placeholder="Tell us about yourself"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Password Change -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">Change Password</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="current_password">
                                        Current Password
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           id="current_password" type="password" name="current_password">
                                </div>

                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="new_password">
                                        New Password
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           id="new_password" type="password" name="new_password">
                                </div>
                            </div>
                        </div>

                        <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline"
                                type="submit">
                            Update Profile
                        </button>
                    </form>
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

        // Show popup on page load if there's a message
        window.onload = function() {
            <?php if ($success): ?>
                showPopup(<?php echo json_encode($success); ?>);
            <?php endif; ?>
            <?php if ($error): ?>
                showPopup(<?php echo json_encode($error); ?>, 'error');
            <?php endif; ?>
        };
    </script>
</body>
</html> 