<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Check if employee_id is set in the POST request
if (isset($_POST['employee_id'])) {
    $employeeId = (int)$_POST['employee_id'];

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $employeeId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Employee account deleted successfully.";

        // Renumber the IDs to fill gaps
        $result = $conn->query("SELECT id FROM accounts ORDER BY id");
        $newId = 1;

        while ($row = $result->fetch_assoc()) {
            $currentId = $row['id'];
            if ($currentId != $newId) {
                // Update the ID only if it's different
                $updateStmt = $conn->prepare("UPDATE accounts SET id = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $newId, $currentId);
                $updateStmt->execute();
                $updateStmt->close();
            }
            $newId++;
        }

        // Reset the auto-increment to the next available ID
        $conn->query("ALTER TABLE accounts AUTO_INCREMENT = $newId");

    } else {
        $_SESSION['message'] = "Error deleting employee account: " . $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['message'] = "No employee ID provided.";
}

// Redirect back to the employee accounts page
header('Location: employee_accounts.php');
exit();
?>
