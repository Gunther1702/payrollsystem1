<?php
session_start();
require 'db_connection.php'; // Include your database connection file

if (!isset($_SESSION['emp_id'])) {
    die("You are not logged in.");
}

// Get employee details from session
$emp_id = $_SESSION['emp_id'];

// Fetch the complete employee record
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

$stmt->close();
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Payroll Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            background: #e0f7fa; /* Light turquoise background */
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #00796b; /* Dark turquoise */
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
            left: -250px;
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar h3 {
            margin: 0 0 20px;
            font-size: 24px;
            color: #ffffff; /* White text */
        }

        .sidebar p {
            margin: 15px 0;
            font-size: 18px;
            color: #b2dfdb; /* Light turquoise */
        }

        .sidebar a {
            color: #ffffff; /* White for links */
            text-decoration: none;
            display: block;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s, color 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #004d40; /* Darker turquoise on hover */
            color: #ffffff; /* White text */
        }

        .content {
            margin-left: 20px;
            padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center; /* Center the header content */
            background: #009688; /* Bright turquoise for header */
            color: white;
            padding: 10px 20px;
            position: relative; /* For positioning the toggle button */
        }

        .header h1 {
            margin: 0;
        }

        .toggle-btn {
            position: absolute; /* Change to absolute positioning */
            left: 20px; /* Adjust position to the left */
            top: 50%; /* Center vertically */
            transform: translateY(-50%); /* Align vertically */
            background: #00796b; /* Darker turquoise for toggle button */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
        }

        .dashboard-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: #ffffff; /* White background for table */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .dashboard-table th, .dashboard-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .dashboard-table th {
            background: #009688; /* Bright turquoise for table headers */
            color: #fff;
        }

        @media (max-width: 600px) {
            .sidebar {
                width: 100%;
                left: -100%;
            }
            .sidebar.active {
                left: 0;
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body onclick="closeSidebar(event)">

<div class="sidebar" id="sidebar">
    <h3>Payroll Management System</h3>
    <p><?php echo htmlspecialchars($employee['fullname']); ?></p>
    <nav class="menu-bar">
        <a href="employee_dashboard.php" class="active">Employee Dashboard</a>
        <a href="edit_your_acc.php">Update Your Account</a>
        <a href="log_out.php">Log Out</a>
    </nav>
</div>

<div class="content">
    <div class="header">
        <button class="toggle-btn" onclick="toggleSidebar(event)">☰</button>
        <h1>Payroll Ni Bossing</h1>
    </div>

    <table class="dashboard-table">
        <tr>
            <th>Employee ID</th>
            <td><?php echo htmlspecialchars($employee['emp_id']); ?></td>
        </tr>
        <tr>
            <th>Full Name</th>
            <td><?php echo htmlspecialchars($employee['fullname']); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($employee['email']); ?></td>
        </tr>
        <tr>
            <th>Gender</th>
            <td><?php echo htmlspecialchars($employee['gender']); ?></td>
        </tr>
        <tr>
            <th>Employee Type</th>
            <td><?php echo htmlspecialchars($employee['emp_type']); ?></td>
        </tr>
        <tr>
            <th>Department</th>
            <td><?php echo htmlspecialchars($employee['department']); ?></td>
        </tr>
        <tr>
            <th>Deductions</th>
            <td><?php echo htmlspecialchars($employee['deduction']); ?></td>
        </tr>
        <tr>
            <th>Overtime</th>
            <td><?php echo htmlspecialchars($employee['overtime']); ?> hrs</td>
        </tr>
        <tr>
            <th>Bonus</th>
            <td><?php echo htmlspecialchars($employee['bonus']); ?></td>
        </tr>
        <tr>
            <th>Contact</th>
            <td><?php echo htmlspecialchars($employee['contact']); ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?php echo htmlspecialchars($employee['address']); ?></td>
        </tr>
        <tr>
            <th>Net Pay</th>
            <td>₱<?php echo htmlspecialchars(number_format($employee['net_pay'], 2)); ?></td>
        </tr>
    </table>
</div>

<script>
function toggleSidebar(event) {
    event.stopPropagation(); // Prevent the click event from bubbling up
    var sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}

function closeSidebar(event) {
    var sidebar = document.getElementById('sidebar');
    if (sidebar.classList.contains('active') && !sidebar.contains(event.target) && !event.target.classList.contains('toggle-btn')) {
        sidebar.classList.remove('active');
    }
}
</script>

</body>
</html>
