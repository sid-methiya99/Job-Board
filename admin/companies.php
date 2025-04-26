<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$industry = $_GET['industry'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$per_page = 10;

// Build query
$query = "SELECT u.*, COUNT(j.id) as total_jobs 
          FROM users u 
          LEFT JOIN jobs j ON u.id = j.employer_id 
          WHERE u.user_type = 'employer'";
$params = [];

if ($search) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.company_industry LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($industry) {
    $query .= " AND u.company_industry = ?";
    $params[] = $industry;
}

$query .= " GROUP BY u.id";

// Get total count for pagination
$count_query = str_replace('SELECT *', 'SELECT COUNT(*) as total', $query);
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_companies = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_companies / $per_page);

// Add pagination and sorting
$query .= " ORDER BY created_at DESC LIMIT " . (int)$per_page . " OFFSET " . (int)(($page - 1) * $per_page);

// Execute main query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique industries for filter
$stmt = $pdo->query("SELECT DISTINCT company_industry FROM users WHERE user_type = 'employer' AND company_industry IS NOT NULL");
$industries = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies - Admin Dashboard</title>
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
                <h1 class="text-3xl font-bold text-gray-800">Manage Companies</h1>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
                <form method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by company name, email, or industry" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-full md:w-48">
                        <select name="industry" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Industries</option>
                            <?php foreach ($industries as $ind): ?>
                            <option value="<?php echo htmlspecialchars($ind); ?>" <?php echo $industry === $ind ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ind); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </form>
            </div>

            <!-- Companies Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Industry</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jobs</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($companies as $company): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if ($company['company_logo']): ?>
                                            <img class="h-10 w-10 rounded-full" src="<?php echo htmlspecialchars($company['company_logo']); ?>" alt="">
                                            <?php else: ?>
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-building text-gray-500"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($company['name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($company['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($company['company_industry'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($company['company_location'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo $company['total_jobs']; ?> Jobs
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view_company.php?id=<?php echo $company['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="edit_company.php?id=<?php echo $company['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_company.php?id=<?php echo $company['id']; ?>" class="text-red-600 hover:text-red-900" 
                                       onclick="return confirm('Are you sure you want to delete this company?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry); ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry); ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium 
                              <?php echo $i === $page ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry); ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 