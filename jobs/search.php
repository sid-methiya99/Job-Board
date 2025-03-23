<?php
session_start();
require_once '../config/database.php';

$search = $_GET['q'] ?? '';
$location = $_GET['location'] ?? '';

// Build search query
$sql = "
    SELECT jobs.*, 
           users.name as company_name, 
           users.company_logo,
           users.company_location
    FROM jobs 
    JOIN users ON jobs.employer_id = users.id 
    WHERE (jobs.title LIKE ? OR jobs.description LIKE ? OR users.name LIKE ?)
";
$params = ["%$search%", "%$search%", "%$search%"];

if ($location) {
    $sql .= " AND jobs.location LIKE ?";
    $params[] = "%$location%";
}

$sql .= " ORDER BY jobs.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <?php include '../includes/navigation.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" class="flex gap-4">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Job title, keywords, or company"
                       class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Search Jobs
                </button>
            </form>
        </div>

        <!-- Results -->
        <h2 class="text-xl font-semibold text-gray-900 mb-6">
            <?php echo count($jobs); ?> results found for "<?php echo htmlspecialchars($search); ?>"
        </h2>

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
                        <a href="../jobs/view.php?id=<?php echo $job['id']; ?>" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            View Details
                        </a>
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
</body>
</html> 