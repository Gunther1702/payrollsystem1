<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Initialize employee variable
$employee = null;

// Fetch employee data if emp_id is provided
if (isset($_GET['emp_id'])) {
    $emp_id = intval($_GET['emp_id']);
    if ($emp_id > 0) {
        $result = mysqli_query($conn, "SELECT * FROM employee WHERE emp_id = $emp_id");
        $employee = mysqli_fetch_assoc($result);

        if (!$employee) {
            die("Employee not found.");
        }
    } else {
        die("Invalid employee ID.");
    }
} else {
    die("Employee ID not provided.");
}

// Handle form submission for editing employee
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $emp_type = mysqli_real_escape_string($conn, $_POST['emp_type']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $emp_id = intval($_POST['emp_id']);

    // Update employee table
    $stmt = $conn->prepare("UPDATE employee SET fullname=?, gender=?, emp_type=?, department=?, contact=?, address=? WHERE emp_id=?");
    $stmt->bind_param("ssssssi", $fullname, $gender, $emp_type, $department, $contact, $address, $emp_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Employee updated successfully."; // Set session message
        header("Location: admin_dashboard.php"); // Redirect to the dashboard
        exit();
    } else {
        echo "Error updating employee record: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - Payroll Management System</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 20px;
        }

        .modal-overlay {
            display: flex;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-container {
            background: white;
            width: 400px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .modal-header {
            margin-bottom: 15px;
            position: relative;
        }

        h2 {
            margin: 0;
            color: #007bff;
            font-size: 20px;
        }

        .modal-close {
            cursor: pointer;
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 24px;
            color: #6c757d;
        }

        label {
            display: block;
            margin: 5px 0 3px;
            text-align: left;
        }

        input[type="text"],
        select {
            width: calc(100% - 16px);
            padding: 8px;
            margin: 3px 0 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal-container" style="background-color: lightblue;">
            <div class="modal-header">
                <h2>Edit Employee</h2>
                <span class="modal-close" onclick="window.history.back()">&times;</span>
            </div>
            <form action="" method="post">
                <input type="hidden" name="emp_id" value="<?php echo $employee['emp_id']; ?>">
                <label for="fullname">Full Name:</label>
                <input type="text" name="fullname" value="<?php echo $employee['fullname']; ?>" required>

                <label for="gender">Gender:</label>
                <select name="gender" required>
                    <option value="Male" <?php echo $employee['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo $employee['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                </select>

                <label for="emp_type">Employee Type:</label>
                <select name="emp_type" required>
                    <option value="Full-time" <?php echo $employee['emp_type'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                    <option value="Part-time" <?php echo $employee['emp_type'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                    <option value="Shift Worker" <?php echo $employee['emp_type'] === 'Shift Worker' ? 'selected' : ''; ?>>Shift Worker</option>
                    <option value="Casual" <?php echo $employee['emp_type'] === 'Casual' ? 'selected' : ''; ?>>Casual</option>
                </select>

                <label for="department">Department:</label>
                <select name="department" required>
                    <option value="Human Resource" <?php echo $employee['department'] === 'Human Resource' ? 'selected' : ''; ?>>Human Resource</option>
                    <option value="Maintenance" <?php echo $employee['department'] === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="Marketing" <?php echo $employee['department'] === 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                    <option value="Finance" <?php echo $employee['department'] === 'Finance' ? 'selected' : ''; ?>>Finance</option>
                </select>

                <label for="contact">Contact:</label>
                <input type="text" name="contact" value="<?php echo $employee['contact']; ?>" required>

                <label for="address">Address:</label>
                <input type="text" name="address" value="<?php echo $employee['address']; ?>" required>

                <input type="submit" value="Edit Employee">
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // If there is a success message in the session, show the alert
            <?php if (isset($_SESSION['message'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo $_SESSION['message']; unset($_SESSION['message']); ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
