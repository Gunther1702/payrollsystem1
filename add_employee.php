<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $fullname = $_POST['fullname'];
    $gender = $_POST['gender'];
    $department = $_POST['department'];
    $emp_type = $_POST['emp_type'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $email = $_POST['email'];

    // Check if the email already exists
    $email_query = "SELECT COUNT(*) AS count FROM employee WHERE email = '$email'";
    $email_result = mysqli_query($conn, $email_query);
    $email_row = mysqli_fetch_assoc($email_result);

    if ($email_row['count'] > 0) {
        // Email already exists, set an error message
        $_SESSION['message'] = 'Error: This email is already associated with an existing employee.';
        header('Location: admin_dashboard.php');
        exit();
    }

    // Find the maximum emp_id currently in use
    $max_query = "SELECT MAX(emp_id) AS max_emp_id FROM employee";
    $result = mysqli_query($conn, $max_query);
    $row = mysqli_fetch_assoc($result);
    $new_emp_id = ($row['max_emp_id'] ? $row['max_emp_id'] : 0) + 1;

    // Insert new employee into the database with the new emp_id
    $query = "INSERT INTO employee (emp_id, fullname, gender, department, emp_type, contact, address, email) 
              VALUES ('$new_emp_id', '$fullname', '$gender', '$department', '$emp_type', '$contact', '$address', '$email')";

    if (mysqli_query($conn, $query)) {
        // Set a session message for success
        $_SESSION['message'] = 'Employee added successfully!';
        // Redirect back to the admin dashboard
        header('Location: admin_dashboard.php');
        exit();
    } else {
        // Set a session message for error
        $_SESSION['message'] = 'Error adding employee: ' . mysqli_error($conn);
        header('Location: admin_dashboard.php');
        exit();
    }
}

mysqli_close($conn);
?>
