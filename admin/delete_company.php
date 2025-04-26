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
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete company's jobs
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE employer_id = ?");
        $stmt->execute([$company_id]);

        // Delete company's applications
        $stmt = $pdo->prepare("DELETE FROM applications WHERE job_id IN (SELECT id FROM jobs WHERE employer_id = ?)");
        $stmt->execute([$company_id]);

        // Delete company
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$company_id]);

        // Commit transaction
        $pdo->commit();

        // Redirect to companies list with success message
        $_SESSION['success_message'] = 'Company and all associated data have been deleted successfully.';
        header('Location: companies.php');
        exit();
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = 'Error deleting company: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Company - Admin Dashboard</title>
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
                <h1 class="text-3xl font-bold text-gray-800">Delete Company</h1>
                <div class="flex items-center space-x-4">
                    <a href="view_company.php?id=<?php echo $company_id; ?>" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Company
                    </a>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Confirm Deletion</h2>
                    <p class="text-gray-600 mb-4">Are you sure you want to delete this company? This action will also delete all associated jobs and applications. This action cannot be undone.</p>
                    
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="font-medium text-gray-800 mb-2">Company Details</h3>
                        <p class="text-gray-600"><span class="font-medium">Name:</span> <?php echo htmlspecialchars($company['company_name']); ?></p>
                        <p class="text-gray-600"><span class="font-medium">Contact:</span> <?php echo htmlspecialchars($company['name']); ?></p>
                        <p class="text-gray-600"><span class="font-medium">Email:</span> <?php echo htmlspecialchars($company['email']); ?></p>
                    </div>

                    <form method="POST" class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="confirm" name="confirm" required class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="confirm" class="ml-2 block text-sm text-gray-700">
                                I understand that this action cannot be undone
                            </label>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="view_company.php?id=<?php echo $company_id; ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Delete Company
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 