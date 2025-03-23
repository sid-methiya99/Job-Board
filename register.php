<?php
session_start();
require_once 'config/database.php';

$type = $_GET['type'] ?? '';
if (!in_array($type, ['employer', 'jobseeker'])) {
    // Show selection page if type not specified
    include 'includes/register_choice.php';
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception("Email already registered");
        }

        // Start transaction
        $pdo->beginTransaction();

        if ($user_type == 'employer') {
            // Company registration
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, user_type, company_website, company_size, company_industry, company_location) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name,
                $email,
                $password,
                'employer',
                $_POST['company_website'],
                $_POST['company_size'],
                $_POST['company_industry'],
                $_POST['company_location']
            ]);
        } else {
            // Job seeker registration
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, user_type, skills, experience, linkedin_url, github_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name,
                $email,
                $password,
                'jobseeker',
                $_POST['skills'],
                $_POST['experience'],
                $_POST['linkedin_url'],
                $_POST['github_url']
            ]);
        }

        $pdo->commit();
        $success = "Registration successful! Please login.";
        header("refresh:2;url=login.php");
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($type); ?> Registration - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/navigation.php'; ?>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    <?php echo $type == 'employer' ? 'Register Your Company' : 'Create Job Seeker Account'; ?>
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        sign in to your account
                    </a>
                </p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST" action="">
                <input type="hidden" name="user_type" value="<?php echo $type; ?>">
                
                <!-- Common Fields -->
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            <?php echo $type == 'employer' ? 'Company Name' : 'Full Name'; ?>
                        </label>
                        <input id="name" name="name" type="text" required 
                               class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input id="email" name="email" type="email" required 
                               class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" name="password" type="password" required 
                               class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                    </div>
                </div>

                <?php if ($type == 'employer'): ?>
                    <!-- Employer-specific fields -->
                    <div class="space-y-4">
                        <div>
                            <label for="company_website" class="block text-sm font-medium text-gray-700">Company Website</label>
                            <input id="company_website" name="company_website" type="url" 
                                   class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                        </div>
                        <div>
                            <label for="company_size" class="block text-sm font-medium text-gray-700">Company Size</label>
                            <select id="company_size" name="company_size" 
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select size</option>
                                <option value="1-10">1-10 employees</option>
                                <option value="11-50">11-50 employees</option>
                                <option value="51-200">51-200 employees</option>
                                <option value="201-500">201-500 employees</option>
                                <option value="501+">501+ employees</option>
                            </select>
                        </div>
                        <div>
                            <label for="company_industry" class="block text-sm font-medium text-gray-700">Industry</label>
                            <input id="company_industry" name="company_industry" type="text" 
                                   class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                        </div>
                        <div>
                            <label for="company_location" class="block text-sm font-medium text-gray-700">Location</label>
                            <input id="company_location" name="company_location" type="text" 
                                   class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Job seeker-specific fields -->
                    <div class="space-y-4">
                        <div>
                            <label for="skills" class="block text-sm font-medium text-gray-700">Skills (comma-separated)</label>
                            <input id="skills" name="skills" type="text" 
                                   class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                   placeholder="e.g., PHP, JavaScript, Project Management">
                        </div>
                        <div>
                            <label for="experience" class="block text-sm font-medium text-gray-700">Work Experience</label>
                            <textarea id="experience" name="experience" rows="3" 
                                      class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                      placeholder="Brief description of your work experience"></textarea>
                        </div>
                        <div>
                            <label for="linkedin_url" class="block text-sm font-medium text-gray-700">LinkedIn Profile URL</label>
                            <input id="linkedin_url" name="linkedin_url" type="url" 
                                   class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                        </div>
                        <div>
                            <label for="github_url" class="block text-sm font-medium text-gray-700">GitHub Profile URL</label>
                            <input id="github_url" name="github_url" type="url" 
                                   class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 