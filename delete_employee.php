<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Get employee ID from the query string
$emp_id = $_GET['id'];

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Perform deletion
    $query = "DELETE FROM employee WHERE emp_id = '$emp_id'";
    if (!mysqli_query($conn, $query)) {
        throw new Exception('Error deleting employee: ' . mysqli_error($conn));
    }

    // Get the remaining employees
    $result = mysqli_query($conn, "SELECT emp_id FROM employee ORDER BY emp_id ASC");
    
    $new_id = 1; // Start reassigning from 1
    while ($row = mysqli_fetch_assoc($result)) {
        $current_id = $row['emp_id'];
        // Only update if the current ID does not match the new ID
        if ($current_id !== $new_id) {
            $update_query = "UPDATE employee SET emp_id = '$new_id' WHERE emp_id = '$current_id'";
            if (!mysqli_query($conn, $update_query)) {
                throw new Exception('Error updating employee ID: ' . mysqli_error($conn));
            }
        }
        $new_id++;
    }

    // Commit transaction
    mysqli_commit($conn);
    $_SESSION['message'] = 'Employee deleted and IDs updated successfully.';
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['message'] = $e->getMessage();
}

header('Location: admin_dashboard.php');
exit();
?>
