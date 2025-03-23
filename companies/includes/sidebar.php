<div class="bg-blue-800 text-white w-64 py-6 flex flex-col">
    <div class="px-6 mb-8">
        <h2 class="text-2xl font-bold">JobPortal</h2>
    </div>
    <nav class="flex-1">
        <a href="dashboard.php" class="flex items-center px-6 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-blue-900' : 'hover:bg-blue-900'; ?>">
            <i class="fas fa-tachometer-alt mr-3"></i>
            Dashboard
        </a>
        <a href="post-job.php" class="flex items-center px-6 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'post-job.php' ? 'bg-blue-900' : 'hover:bg-blue-900'; ?>">
            <i class="fas fa-plus-circle mr-3"></i>
            Post New Job
        </a>
        <a href="applications.php" class="flex items-center px-6 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'applications.php' ? 'bg-blue-900' : 'hover:bg-blue-900'; ?>">
            <i class="fas fa-users mr-3"></i>
            Applications
        </a>
        <a href="company-profile.php" class="flex items-center px-6 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'company-profile.php' ? 'bg-blue-900' : 'hover:bg-blue-900'; ?>">
            <i class="fas fa-building mr-3"></i>
            Company Profile
        </a>
    </nav>
    <div class="px-6 py-4">
        <a href="logout.php" class="flex items-center text-white hover:text-gray-200">
            <i class="fas fa-sign-out-alt mr-3"></i>
            Logout
        </a>
    </div>
</div> 