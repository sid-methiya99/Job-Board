<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    header('Location: users.php');
    exit();
}

// Check if user exists
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: users.php');
    exit();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete user's applications
        $stmt = $pdo->prepare("DELETE FROM applications WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Delete user's jobs if they are an employer
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE employer_id = ?");
        $stmt->execute([$user_id]);

        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Commit transaction
        $pdo->commit();

        // Redirect with success message
        header('Location: users.php?message=' . urlencode('User deleted successfully.'));
        exit();
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = 'Error deleting user: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - Admin Dashboard</title>
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
                <a href="users.php" class="block py-2.5 px-4 rounded bg-blue-900">
                    <i class="fas fa-users mr-2"></i>Users
                </a>
                <a href="companies.php" class="block py-2.5 px-4 rounded hover:bg-blue-700">
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
                <h1 class="text-3xl font-bold text-gray-800">Delete User</h1>
                <div class="flex items-center space-x-4">
                    <a href="users.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Users
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Are you sure you want to delete this user?</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        This action cannot be undone. This will permanently delete the user account and all associated data.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <a href="users.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <form method="POST" class="inline">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Delete User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 