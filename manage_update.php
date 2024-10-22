<?php
session_start();
include('db.php'); // Include database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Get the posted data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $deduction_id = mysqli_real_escape_string($conn, $_POST['deduction_id']);
    $healthinsurance = mysqli_real_escape_string($conn, $_POST['healthinsurance']);
    $loans = mysqli_real_escape_string($conn, $_POST['loans']);
    $others = mysqli_real_escape_string($conn, $_POST['others']);

    // Update the database
    $query = "UPDATE deductions SET healthinsurance='$healthinsurance', loans='$loans', others='$others' WHERE deduction_id='$deduction_id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Deduction updated successfully.";
    } else {
        $_SESSION['message'] = "Error updating deduction: " . mysqli_error($conn);
    }

    mysqli_close($conn);
    header("Location: manage_deduction.php");
    exit();
}
?>
