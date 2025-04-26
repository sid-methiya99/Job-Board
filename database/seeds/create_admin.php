<?php
require_once __DIR__ . '/../../config/database.php';

try {
    // Default admin credentials
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT); // You should change this password
    $email = 'admin@example.com';

    // Prepare SQL statement
    $stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
    
    // Execute the statement
    if ($stmt->execute([$username, $password, $email])) {
        echo "Admin user created successfully\n";
    } else {
        echo "Error creating admin user\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 