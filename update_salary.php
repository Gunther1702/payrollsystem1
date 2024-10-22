<?php
session_start();
include('db.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullTimeSalary = $_POST['full_time_salary'];
    $partTimeSalary = $_POST['part_time_salary'];
    $casualSalary = $_POST['casual_salary'];
    $shiftWorkerSalary = $_POST['shift_worker']; // This should match the input name

    // Debugging output to check values
    error_log("Full-Time Salary: $fullTimeSalary");
    error_log("Part-Time Salary: $partTimeSalary");
    error_log("Casual Salary: $casualSalary");
    error_log("Shift Worker Salary: $shiftWorkerSalary");

    // Update the salary rates in the database
    $query = "UPDATE salary SET full_time_salary = ?, part_time_salary = ?, casual = ?, shift_worker = ? WHERE salary_id = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dddd", $fullTimeSalary, $partTimeSalary, $casualSalary, $shiftWorkerSalary);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Salary rates updated successfully!";
    } else {
        $_SESSION['message'] = "Failed to update salary rates.";
    }

    $stmt->close();
    header('Location: payroll_section.php');
    exit();
}


?>
