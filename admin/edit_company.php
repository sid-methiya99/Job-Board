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

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $company_name = trim($_POST['company_name']);
    $company_website = trim($_POST['company_website']);
    $company_size = trim($_POST['company_size']);
    $company_industry = trim($_POST['company_industry']);
    $company_location = trim($_POST['company_location']);

    // Validate input
    if (empty($name) || empty($email) || empty($company_name)) {
        $error = 'Name, email, and company name are required.';
    } else {
        try {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $company_id]);
            if ($stmt->fetch()) {
                $error = 'Email is already taken by another user.';
            } else {
                // Update company
                $sql = "UPDATE users SET 
                        name = ?, 
                        email = ?,
                        company_name = ?,
                        company_website = ?,
                        company_size = ?,
                        company_industry = ?,
                        company_location = ?
                        WHERE id = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $name, $email,
                    $company_name, $company_website,
                    $company_size, $company_industry,
                    $company_location,
                    $company_id
                ]);

                $message = 'Company updated successfully.';
                
                // Refresh company data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$company_id]);
                $company = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $error = 'Error updating company: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Company - Admin Dashboard</title>
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
                <h1 class="text-3xl font-bold text-gray-800">Edit Company</h1>
                <div class="flex items-center space-x-4">
                    <a href="view_company.php?id=<?php echo $company_id; ?>" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Company
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($company['name']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($company['email']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company Name</label>
                            <input type="text" name="company_name" value="<?php echo htmlspecialchars($company['company_name']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company Website</label>
                            <input type="url" name="company_website" value="<?php echo htmlspecialchars($company['company_website']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company Size</label>
                            <input type="text" name="company_size" value="<?php echo htmlspecialchars($company['company_size']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Industry</label>
                            <input type="text" name="company_industry" value="<?php echo htmlspecialchars($company['company_industry']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <input type="text" name="company_location" value="<?php echo htmlspecialchars($company['company_location']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="view_company.php?id=<?php echo $company_id; ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Update Company
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 