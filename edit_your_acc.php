<?php
session_start();
require 'db_connection.php'; // Include your database connection file

if (!isset($_SESSION['emp_id'])) {
    die("You are not logged in.");
}

// Get employee details from session
$emp_id = $_SESSION['emp_id'];

// Fetch the employee record
$stmt = $conn->prepare("SELECT * FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $employee = $result->fetch_assoc();
} else {
    echo "No employee found with this ID.";
    exit;
}

// Fetch the associated account record
$account_stmt = $conn->prepare("SELECT * FROM accounts WHERE username = ?");
$account_stmt->bind_param("s", $employee['email']);
$account_stmt->execute();
$account_result = $account_stmt->get_result();

if ($account_result->num_rows === 1) {
    $account = $account_result->fetch_assoc();
} else {
    echo "No account found for this employee.";
    exit;
}

// Initialize message variables
$message = "";
$messageType = ""; // 'success' or 'error'
$redirect = false; // Control redirection

// Update employee details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $username = $email; // Set username to the new email

    // Validate input
    if (empty($fullname) || empty($email) || empty($contact) || empty($address)) {
        $message = "All fields are required.";
        $messageType = 'error';
    } else {
        // Check if the email already exists
        $email_check_stmt = $conn->prepare("SELECT * FROM employee WHERE email = ? AND emp_id != ?");
        $email_check_stmt->bind_param("si", $email, $emp_id);
        $email_check_stmt->execute();
        $email_check_result = $email_check_stmt->get_result();

        if ($email_check_result->num_rows > 0) {
            $message = "This email address already exists.";
            $messageType = 'error';
        } else {
            // Update the employee record
            $update_employee_stmt = $conn->prepare("UPDATE employee SET fullname = ?, email = ?, contact = ?, address = ? WHERE emp_id = ?");
            $update_employee_stmt->bind_param("ssssi", $fullname, $email, $contact, $address, $emp_id);

            // Update the account record
            $update_account_stmt = $conn->prepare("UPDATE accounts SET username = ? WHERE id = ?");
            $update_account_stmt->bind_param("si", $username, $account['id']);

            // Execute both updates
            if ($update_employee_stmt->execute() && $update_account_stmt->execute()) {
                $message = "Account updated successfully!";
                $messageType = 'success';
                $redirect = true; // Set redirect to true
            } else {
                $message = "Error updating account: " . $conn->error;
                $messageType = 'error';
            }

            $update_employee_stmt->close();
            $update_account_stmt->close();
        }
        $email_check_stmt->close();
    }
}

$stmt->close();
$account_stmt->close();
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Your Account - Payroll Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #address {
            overflow: hidden;
            height: auto;
            min-height: 100px;
        }

        .form-control {
            resize: none;
            min-height: 10px;
            overflow: auto;
        }

        body {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            max-width: 500px;
            margin: 100px auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close {
            cursor: pointer;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 15px;
            height: auto;
            max-height: 150px;
        }

        .btn-primary {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="modal-container" style="background: #e0f7fa;">
    <div class="modal-header">
        <h2>Edit Your Account</h2>
        <span class="close" onclick="window.location.href='employee_dashboard.php'">&times;</span>
    </div>
    <form method="POST" action="">
        <div class="form-group">
            <label for="fullname">Full Name</label>
            <input type="text" name="fullname" class="form-control" id="fullname" value="<?php echo htmlspecialchars($employee['fullname']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control" id="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="contact">Contact</label>
            <input type="text" name="contact" class="form-control" id="contact" value="<?php echo htmlspecialchars($employee['contact']); ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea name="address" class="form-control" id="address" required><?php echo htmlspecialchars($employee['address']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
    <a href="employee_dashboard.php" class="btn btn-secondary mt-3" style="width: 100%;">Cancel</a>
</div>

<?php if ($message): ?>
<script>
    Swal.fire({
        icon: '<?php echo $messageType; ?>',
        title: '<?php echo $messageType === 'success' ? 'Success!' : 'Oops...'; ?>',
        text: '<?php echo $message; ?>',
        allowOutsideClick: false,
        willClose: () => {
            <?php if ($redirect): ?>
                window.location.href = 'employee_dashboard.php';
            <?php endif; ?>
        }
    });
</script>
<?php endif; ?>

</body>
</html>
