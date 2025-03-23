<?php
session_start();
require_once '../config/database.php';

// Get search parameters
$search = $_GET['q'] ?? '';
$location = $_GET['location'] ?? '';
$category = $_GET['category'] ?? '';

// Build the SQL query
$sql = "
    SELECT jobs.*, 
           users.name as company_name, 
           users.company_logo,
           users.company_location
    FROM jobs 
    JOIN users ON jobs.employer_id = users.id 
    WHERE 1=1
";
$params = [];

if ($search) {
    $sql .= " AND (jobs.title LIKE ? OR jobs.description LIKE ? OR users.name LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($location) {
    $sql .= " AND jobs.location LIKE ?";
    $params[] = "%$location%";
}

if ($category) {
    $sql .= " AND jobs.category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY jobs.created_at DESC";

// Execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Get unique locations for filter
$stmt = $pdo->query("SELECT DISTINCT location FROM jobs ORDER BY location");
$locations = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get job categories
$categories = ['Technology', 'Marketing', 'Sales', 'Design', 'Engineering', 'Finance', 'Healthcare', 'Other'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex-shrink-0 flex items-center">
                        <i class="fas fa-briefcase text-blue-600 text-2xl mr-2"></i>
                        <span class="text-xl font-bold text-gray-800">JobPortal</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_type'] == 'employer'): ?>
                            <a href="../companies/dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <?php else: ?>
                            <a href="../users/dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <?php endif; ?>
                        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Logout</a>
                    <?php else: ?>
                        <a href="../login.php" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="../register.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Jobs</label>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Job title or keywords"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <select name="location" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc); ?>" 
                                        <?php echo $location == $loc ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"
                                        <?php echo $category == $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Search Jobs
                    </button>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">
                <?php echo count($jobs); ?> Jobs Found
                <?php if ($search || $location || $category): ?>
                    <span class="text-gray-500 text-base font-normal">
                        for your search
                    </span>
                <?php endif; ?>
            </h2>
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <span>Sort by:</span>
                <select class="border rounded px-2 py-1">
                    <option>Most Recent</option>
                    <option>Relevance</option>
                </select>
            </div>
        </div>

        <!-- Job Listings -->
        <div class="space-y-6">
            <?php foreach ($jobs as $job): ?>
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <?php if (!empty($job['company_logo'])): ?>
                                <img src="../uploads/logos/<?php echo htmlspecialchars($job['company_logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                     class="w-16 h-16 object-contain rounded">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas fa-building text-gray-400 text-2xl"></i>
                                </div>
                            <?php endif; ?>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </h3>
                                <p class="text-gray-600"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                <div class="mt-2 flex items-center text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <?php echo htmlspecialchars($job['location']); ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-money-bill-alt mr-2"></i>
                                    <?php echo htmlspecialchars($job['salary']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-sm text-gray-500">
                                Posted <?php echo time_elapsed_string($job['created_at']); ?>
                            </span>
                            <a href="view.php?id=<?php echo $job['id']; ?>" 
                               class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                View Details
                            </a>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-gray-600">
                        <?php 
                        $description = strip_tags($job['description']);
                        echo strlen($description) > 200 ? substr($description, 0, 200) . '...' : $description;
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    // Helper function to format dates
    function time_elapsed_string($datetime) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        if ($diff->d == 0) {
            if ($diff->h == 0) {
                if ($diff->i == 0) {
                    return "just now";
                }
                return $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
            }
            return $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
        }
        if ($diff->d < 7) {
            return $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
        }
        if ($diff->d < 30) {
            return floor($diff->d/7) . " week" . (floor($diff->d/7) > 1 ? "s" : "") . " ago";
        }
        return $ago->format('M j, Y');
    }
    ?>
</body>
</html> 