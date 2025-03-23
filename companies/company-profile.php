<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Fetch company details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $website = $_POST['company_website'];
        $size = $_POST['company_size'];
        $industry = $_POST['company_industry'];
        $location = $_POST['company_location'];
        $description = $_POST['company_description'];

        // Handle logo upload
        $logo_path = $company['company_logo']; // Keep existing logo by default
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['company_logo']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($filetype, $allowed)) {
                $new_filename = uniqid() . '.' . $filetype;
                $upload_path = '../uploads/logos/' . $new_filename;
                
                // Create logos directory if it doesn't exist
                if (!file_exists('../uploads/logos')) {
                    mkdir('../uploads/logos', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_path)) {
                    $logo_path = $new_filename;
                }
            }
        }

        // Update profile
        $stmt = $pdo->prepare("
            UPDATE users SET 
            name = ?,
            email = ?,
            company_website = ?,
            company_size = ?,
            company_industry = ?,
            company_location = ?,
            company_description = ?,
            company_logo = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $name,
            $email,
            $website,
            $size,
            $industry,
            $location,
            $description,
            $logo_path,
            $_SESSION['user_id']
        ]);

        $_SESSION['success_message'] = "Company profile updated successfully!";
        header("Location: company-profile.php");
        exit();

    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Get success message from session
$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile - Job Portal</title>
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
    <div id="statusPopup" class="popup">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="popupMessage"></span>
        </div>
    </div>

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <div class="container mx-auto px-6 py-8">
                <h3 class="text-gray-700 text-3xl font-medium mb-6">Company Profile</h3>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- Company Logo -->
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Company Logo
                            </label>
                            <?php if (!empty($company['company_logo'])): ?>
                                <div class="mb-4">
                                    <img src="../uploads/logos/<?php echo htmlspecialchars($company['company_logo']); ?>" 
                                         alt="Company Logo" 
                                         class="h-24 w-24 object-contain border rounded">
                                </div>
                            <?php endif; ?>
                            <input type="file" 
                                   name="company_logo" 
                                   accept="image/png, image/jpeg"
                                   class="w-full">
                            <p class="text-sm text-gray-500 mt-1">Recommended size: 200x200px. PNG or JPG only.</p>
                        </div>

                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">Basic Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Company Name
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           value="<?php echo htmlspecialchars($company['name']); ?>" 
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Email
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($company['email']); ?>" 
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Company Details -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">Company Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Website
                                    </label>
                                    <input type="url" 
                                           name="company_website" 
                                           value="<?php echo htmlspecialchars($company['company_website'] ?? ''); ?>" 
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Company Size
                                    </label>
                                    <select name="company_size" 
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <option value="">Select size</option>
                                        <option value="1-10" <?php echo ($company['company_size'] ?? '') == '1-10' ? 'selected' : ''; ?>>1-10 employees</option>
                                        <option value="11-50" <?php echo ($company['company_size'] ?? '') == '11-50' ? 'selected' : ''; ?>>11-50 employees</option>
                                        <option value="51-200" <?php echo ($company['company_size'] ?? '') == '51-200' ? 'selected' : ''; ?>>51-200 employees</option>
                                        <option value="201-500" <?php echo ($company['company_size'] ?? '') == '201-500' ? 'selected' : ''; ?>>201-500 employees</option>
                                        <option value="501+" <?php echo ($company['company_size'] ?? '') == '501+' ? 'selected' : ''; ?>>501+ employees</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Industry
                                    </label>
                                    <input type="text" 
                                           name="company_industry" 
                                           value="<?php echo htmlspecialchars($company['company_industry'] ?? ''); ?>" 
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Location
                                    </label>
                                    <input type="text" 
                                           name="company_location" 
                                           value="<?php echo htmlspecialchars($company['company_location'] ?? ''); ?>" 
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                            </div>
                        </div>

                        <!-- Company Description -->
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Company Description
                            </label>
                            <textarea name="company_description" 
                                      rows="6" 
                                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($company['company_description'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline">
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

        // Show popup if there's a message
        <?php if ($success): ?>
            showPopup(<?php echo json_encode($success); ?>);
        <?php endif; ?>
        <?php if ($error): ?>
            showPopup(<?php echo json_encode($error); ?>, 'error');
        <?php endif; ?>
    </script>
</body>
</html> 