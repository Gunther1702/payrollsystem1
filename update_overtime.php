<?php
session_start();
include('db.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $overtimeRate = $_POST['overtime_rate'];

    // Update the overtime rate in the database
    $query = "UPDATE overtime SET rate = ? WHERE ot_id = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("d", $overtimeRate);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Overtime rate updated successfully!";
    } else {
        $_SESSION['message'] = "Failed to update overtime rate.";
    }

    $stmt->close();
    header('Location: payroll_section.php');
    exit();
}
?>
