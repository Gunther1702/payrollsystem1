<?php
session_start();
include('db.php'); // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $selected_role = trim($_POST['role']);

    // Check if inputs are not empty
    if (empty($username) || empty($password) || empty($selected_role)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Check the user in the database
    $stmt = $conn->prepare("SELECT * FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if the role matches
            if ($user['role'] === $selected_role) {
                session_regenerate_id(true);
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $username; // Save email for later use

                // If role is employee, check the employee table
                if ($user['role'] === 'employee') {
                    $employeeStmt = $conn->prepare("SELECT * FROM employee WHERE email = ?");
                    $employeeStmt->bind_param("s", $username);
                    $employeeStmt->execute();
                    $employeeResult = $employeeStmt->get_result();

                    if ($employeeResult->num_rows === 1) {
                        $employee = $employeeResult->fetch_assoc();
                        $_SESSION['emp_id'] = $employee['emp_id']; // Save emp_id
                        $_SESSION['fullname'] = $employee['fullname']; // Save full name
                        $_SESSION['net_pay'] = $employee['net_pay']; // Save net pay

                        // Redirect to employee dashboard
                        echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => 'employee_dashboard.php']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'No employee record found for this email.']);
                    }

                    $employeeStmt->close();
                } else {
                    // Redirect based on admin role
                    $redirectUrl = 'admin_dashboard.php';
                    echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => $redirectUrl]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Role mismatch.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Username or password is incorrect.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Username or password is incorrect.']);
    }
    $stmt->close();
}

mysqli_close($conn);
?>
