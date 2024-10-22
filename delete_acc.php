<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Check if employee_id is set
if (isset($_POST['employee_id'])) {
    $employeeId = (int)$_POST['employee_id'];
    
    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $employeeId);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Employee account deleted successfully.';
    } else {
        $_SESSION['message'] = 'Error deleting employee account: ' . $conn->error;
    }
    
    $stmt->close();
}

// Redirect back to the employee accounts page
header('Location: employee_accounts.php');
exit();
?>
