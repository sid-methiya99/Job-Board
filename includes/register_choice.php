<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'navigation.php'; ?>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">Choose Account Type</h2>
                <p class="mt-2 text-gray-600">Select how you want to use JobPortal</p>
            </div>

            <div class="mt-8 space-y-4">
                <a href="register.php?type=jobseeker" 
                   class="w-full flex items-center justify-center px-8 py-6 border border-gray-300 rounded-lg shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-user-tie text-2xl mr-4 text-blue-600"></i>
                    <div class="text-left">
                        <div class="font-semibold">I'm a Job Seeker</div>
                        <div class="text-sm text-gray-500">Looking for job opportunities</div>
                    </div>
                </a>

                <a href="register.php?type=employer" 
                   class="w-full flex items-center justify-center px-8 py-6 border border-gray-300 rounded-lg shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-building text-2xl mr-4 text-blue-600"></i>
                    <div class="text-left">
                        <div class="font-semibold">I'm an Employer</div>
                        <div class="text-sm text-gray-500">Looking to hire talent</div>
                    </div>
                </a>
            </div>

            <p class="mt-4 text-center text-sm text-gray-600">
                Already have an account? 
                <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                    Sign in
                </a>
            </p>
        </div>
    </div>
</body>
</html> 