<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    header('Location: index.php');
    exit();
}

// Check if job ID is provided
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$job_id = $_GET['id'];

// Verify the job belongs to this employer
$stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    header('Location: dashboard.php');
    exit();
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Delete related applications first (due to foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM applications WHERE job_id = ?");
    $stmt->execute([$job_id]);

    // Delete the job
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
    $stmt->execute([$job_id, $_SESSION['user_id']]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success_message'] = "Job deleted successfully";
} catch(PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error_message'] = "Error deleting job";
}

header('Location: dashboard.php');
exit();
?> 