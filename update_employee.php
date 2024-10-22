<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = intval($_POST['emp_id']);
    $overtime_hours = floatval($_POST['overtime_hours']);
    $bonus_amount = floatval($_POST['bonus_amount']);
    $deduction_type = $_POST['deduction']; // Get deduction type

    // Initialize deductions based on the selected type
    $deductions = 0; // Default to zero

    // Handle deductions based on selection
    if ($deduction_type === 'none') {
        // No deductions applied
        $deductions = 0;
    } else {
        // Fetch deduction value based on selected type
        $deduction_value = floatval($_POST['deduction_value']);
        
        if ($deduction_type === 'loans' && !empty($_POST['loan_amount'])) {
            $loan_amount = floatval($_POST['loan_amount']);
            $deductions = $loan_amount; // Use loan amount as deductions
        } else {
            $deductions = $deduction_value; // Use selected deduction value
        }
    }

    // Fetch employee type and salary based on employee ID
    $employee_query = "SELECT emp_type FROM employee WHERE emp_id = ?";
    $stmt = mysqli_prepare($conn, $employee_query);
    mysqli_stmt_bind_param($stmt, "i", $emp_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $emp_type);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Fetch salary rates based on employee type
    $salary_query = "SELECT full_time_salary, part_time_salary, casual, shift_worker FROM salary LIMIT 1";
    $salary_result = mysqli_query($conn, $salary_query);
    $salary_row = mysqli_fetch_assoc($salary_result);

    // Determine salary based on employee type
    switch ($emp_type) {
        case 'Full-time':
            $salary_rate = floatval($salary_row['full_time_salary']);
            break;
        case 'Part-time':
            $salary_rate = floatval($salary_row['part_time_salary']);
            break;
        case 'Casual':
            $salary_rate = floatval($salary_row['casual']);
            break;
        case 'Shift Worker':
            $salary_rate = floatval($salary_row['shift_worker']);
            break;
        default:
            $salary_rate = 0; // Default to 0 if type not recognized
    }

    // Fetch overtime rate
    $overtime_query = "SELECT rate FROM overtime LIMIT 1"; // Assuming only one overtime rate
    $overtime_result = mysqli_query($conn, $overtime_query);
    $overtime_row = mysqli_fetch_assoc($overtime_result);
    $overtime_rate = floatval($overtime_row['rate']);

    // Calculate net pay
    $net_pay = $salary_rate - $deductions + ($overtime_hours * $overtime_rate) + $bonus_amount;

    // Update employee record in the database
    $update_query = "UPDATE employee SET deduction = ?, overtime = ?, bonus = ?, net_pay = ? WHERE emp_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "iiiii", $deductions, $overtime_hours, $bonus_amount, $net_pay, $emp_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Employee updated successfully.";
    } else {
        $_SESSION['message'] = "Error updating employee: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    header('Location: admin_dashboard.php'); // Redirect back to dashboard
    exit();
}
?>
