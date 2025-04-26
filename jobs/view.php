<?php
require_once '../config/database.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../jobs.php');
    exit();
}

$job_id = intval($_GET['id']);

// Get job details
$stmt = $pdo->prepare("SELECT j.*, u.name as company_name, u.company_logo, u.company_website, u.company_location 
                       FROM jobs j 
                       JOIN users u ON j.employer_id = u.id 
                       WHERE j.id = ?");
$stmt->execute([$job_id]);

if ($stmt->rowCount() === 0) {
    header('Location: ../jobs.php');
    exit();
}

$job = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - Job Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-start md:space-x-8">
                    <div class="flex-shrink-0 mb-6 md:mb-0">
                        <?php if ($job['company_logo']): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($job['company_logo']); ?>" 
                                 alt="Company Logo" 
                                 class="w-32 h-32 object-contain rounded-lg border border-gray-200">
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-grow">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($job['title']); ?></h1>
                        <h3 class="text-xl text-blue-600 mb-4"><?php echo htmlspecialchars($job['company_name']); ?></h3>
                        
                        <div class="flex flex-wrap gap-4 mb-6">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?php echo htmlspecialchars($job['location']); ?>
                            </div>
                            <?php if ($job['salary']): ?>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-money-bill-wave mr-2"></i>
                                    <?php echo htmlspecialchars($job['salary']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center text-gray-600">
                                <i class="far fa-calendar-alt mr-2"></i>
                                Posted: <?php echo date('F j, Y', strtotime($job['created_at'])); ?>
                            </div>
                            <?php if ($job['category']): ?>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-tag mr-2"></i>
                                    <?php echo htmlspecialchars($job['category']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 mb-3">Job Description</h2>
                                <div class="prose max-w-none text-gray-600">
                                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                                </div>
                            </div>

                            <?php if ($job['requirements']): ?>
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900 mb-3">Requirements</h2>
                                    <div class="prose max-w-none text-gray-600">
                                        <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="pt-6">
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'jobseeker'): ?>
                                    <a href="../jobs/apply.php?id=<?php echo $job_id; ?>" 
                                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Apply Now
                                    </a>
                                <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <a href="../login.php" 
                                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Login to Apply
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
