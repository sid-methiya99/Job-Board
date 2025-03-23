<?php
require_once '../config/database.php';

// Get company ID from URL
$company_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$company_id) {
    header('Location: ../index.php');
    exit();
}

// Fetch company details
$stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE id = ? AND user_type = 'employer'
");
$stmt->execute([$company_id]);
$company = $stmt->fetch();

if (!$company) {
    header('Location: ../index.php');
    exit();
}

// Fetch active job listings
$stmt = $pdo->prepare("
    SELECT * FROM jobs 
    WHERE employer_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$company_id]);
$jobs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($company['name']); ?> - Company Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Company Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start space-x-6">
                <?php if (!empty($company['company_logo'])): ?>
                    <img src="../uploads/logos/<?php echo htmlspecialchars($company['company_logo']); ?>" 
                         alt="<?php echo htmlspecialchars($company['name']); ?>" 
                         class="h-24 w-24 object-contain">
                <?php endif; ?>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($company['name']); ?></h1>
                    <div class="mt-2 text-gray-600">
                        <?php if (!empty($company['company_location'])): ?>
                            <p><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($company['company_location']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($company['company_website'])): ?>
                            <a href="<?php echo htmlspecialchars($company['company_website']); ?>" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-globe mr-2"></i>Company Website
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Company Info -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">About <?php echo htmlspecialchars($company['name']); ?></h2>
                    <?php if (!empty($company['company_description'])): ?>
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($company['company_description'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Company Stats -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Company Details</h2>
                    <div class="space-y-3">
                        <?php if (!empty($company['company_industry'])): ?>
                            <div>
                                <p class="text-gray-600">Industry</p>
                                <p class="font-medium"><?php echo htmlspecialchars($company['company_industry']); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($company['company_size'])): ?>
                            <div>
                                <p class="text-gray-600">Company Size</p>
                                <p class="font-medium"><?php echo htmlspecialchars($company['company_size']); ?> employees</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open Positions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Open Positions</h2>
            <?php if (empty($jobs)): ?>
                <p class="text-gray-600">No open positions at the moment.</p>
            <?php else: ?>
                <div class="grid gap-4">
                    <?php foreach ($jobs as $job): ?>
                        <div class="border rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h3>
                            <div class="mt-2 text-gray-600">
                                <span class="mr-4"><i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($job['location']); ?></span>
                                <span><i class="fas fa-money-bill-alt mr-1"></i><?php echo htmlspecialchars($job['salary']); ?></span>
                            </div>
                            <a href="../jobs/view.php?id=<?php echo $job['id']; ?>" 
                               class="mt-3 inline-block text-blue-600 hover:text-blue-800">
                                View Details
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 