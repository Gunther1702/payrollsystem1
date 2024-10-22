<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Fetch deduction data
$result = mysqli_query($conn, "SELECT * FROM deductions");
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Initialize total variable
$total_deductions = 0;

// Check for success or error messages
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Deductions - Payroll Management System</title>
    <link rel="stylesheet" href="css/admin_dashboard1.css">
    <link rel="stylesheet" href="css/menu1.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
          .menu-bar a {
            text-decoration: none; /* Remove underline */
            color: inherit; /* Keep the text color */
        }

        .menu-bar a:hover,
        .menu-bar a.active {
            text-decoration: none; /* Ensure no underline on hover and active */
            background: linear-gradient(to right, #1e3c72, #2a69ac);
}
        .header {
            background: linear-gradient(to bottom, #007BFF, #6F42C1);

        }

        thead th {
            position: sticky;
            top: 0;
            background: linear-gradient(to bottom, #007BFF, #6F42C1);
            color: white;
            z-index: 10;
        }
        .btn-primary{
            background: linear-gradient(to bottom, #007BFF, #6F42C1);
        }
         * {
            text-decoration: none;
        }
        .alert {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            background-color: #066cfa; /* Green for success */
        }

        .alert.error {
            background-color: #d9534f; /* Red for error */
        }
        .menu-btn {
    background-color: #395886; /* Button background color */
    color: white; /* Icon color */
    border: black; /* Remove default border */
    border-radius: 10px; /* Rounded corners */
    height: 30px; /* Adjusted height */
    width: 30px; /* Adjusted width */
    font-size: 20px; /* Icon size */
    cursor: pointer; /* Pointer cursor on hover */
    display: flex; /* Flex for centering */
    align-items: center; /* Center vertically */
    justify-content: center; /* Center horizontally */
    transition: background-color 0.3s; /* Smooth background color transition */
}

.menu-btn:hover {
    background-color: black; /* Darker shade on hover */
}

 .main-content header {
        display: flex; /* Use flexbox */
        align-items: center; /* Center items vertically */
        justify-content: space-between; /* Space items evenly */
        padding: 5px; /* Further reduce padding */
    }

    .header-title h1 {
        font-size: 32px; /* Increase font size */
        margin: 0; /* Remove margin */
    }

    .table th {
        background-color: #395886;
        color: white;
        position: sticky;
    }

    .pagination {
        display: flex;
        align-items: center;
    }

    .pagination span {
        margin: 0 10px;
    }

    .page-number {
        padding: 5px 10px;
        border: 1px solid #395886;
        border-radius: 5px;
        background-color: #f1f1f1;
    }
        .header-title h1 {
            margin-top: 10px; /* Add space above the title */
            margin-bottom: 10px; /* Add space below the title */
        }

        h2 {
            margin-top: 20px; /* Space above the Manage Deductions title */
            margin-bottom: 20px; /* Space below the title */
        }

        table {
            border-collapse: collapse; /* Ensure borders don't double up */
            width: 100%; /* Make the table full width */
        }

        th {
            background-color: #395886;
            color: white;
        }

        th, td {
            border: 1px solid #ccc; /* Lighter border color */
            padding: 10px; /* Add padding inside the table cells */
            text-align: left; /* Align text to the left */
        }

        .edit-box {
            display: inline-block; /* Align with the button */
        }

        .total-box {
            border: 1px solid #ccc; /* Border color */
            padding: 20px; /* Padding inside the box */
            background-color: #F5F5F5; /* Light background color */
            width: 100%; /* Full width to match the table */
            box-sizing: border-box; /* Ensure padding is included in width */
        }

        .total-text {
            font-weight: bold; /* Make text bold */
            text-align: right; /* Align text to the right */
            display: block; /* Make it block-level for proper alignment */
        }
    </style>
</head>
<body style="background-color: #F0F8FF;">
    <div class="sidebar" id="sidebar" >
        <h3>
            <img src="css/img/user-gear.png" alt="Admin" style="float: left; width: 60px; height: 50px; margin-right: 10px;"> 
            Admin Dashboard
        </h3>
        <hr>
        <nav class="menu-bar">
            <ul>
                <li><a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-user-friends"></i> Employee Records</a></li>
                <li><a href="manage_deduction.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_deduction.php' ? 'active' : ''; ?>"><i class="fas fa-money-bill-wave"></i> Manage Deduction</a></li>
                <li><a href="payroll_section.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payroll_section.php' ? 'active' : ''; ?>"><i class="fas fa-calculator"></i> Payroll Section</a></li>
                <li><a href="employee_accounts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'employee_accounts.php' ? 'active' : ''; ?>"><i class="fas fa-user-shield"></i> Employee Accounts</a></li>
                <li><a href="log_out.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content" id="mainContent" style="background-color: #F0F8FF;">
        <header>
            <button class="menu-btn" onclick="toggleSidebar()">&#9776;</button>
            <div class="header-title">
                <h1>Payroll Management System</h1>
            </div>
        </header>

        <h2>Manage Deductions</h2>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger'; ?>" id="alertMessage">
                <strong><?php echo strpos($message, 'Error') === false ? 'Success!' : 'Warning!'; ?></strong>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Deduction ID</th>
                    <th>Health Insurance</th>
                    <th>Loans</th>
                    <th>Others</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    // Calculate the total deductions
                    $total_deductions += $row['healthinsurance'] + $row['loans'] + $row['others'];

                    echo "<tr>
                            <td>{$row['deduction_id']}</td>
                            <td>{$row['healthinsurance']}</td>
                            <td>{$row['loans']}</td>
                            <td>{$row['others']}</td>
                            <td>
                                <div class='edit-box'>
                                    <button class='btn btn-primary' data-toggle='modal' data-target='#updateModal' data-id='{$row['deduction_id']}' data-health='{$row['healthinsurance']}' data-loans='{$row['loans']}' data-others='{$row['others']}'>Update</button>
                                </div>
                            </td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="total-box">
            <span class="total-text">Total Deductions: <?php echo number_format($total_deductions); ?></span>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: lightblue;">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Deductions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" >
                    <form id="updateForm" method="POST" action="manage_update.php">
                        <input type="hidden" name="deduction_id" id="deduction_id">
                        <div class="form-group">
                            <label for="healthinsurance">Health Insurance</label>
                            <input type="number" class="form-control" id="healthinsurance" name="healthinsurance" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="others">Others</label>
                            <input type="number" class="form-control" id="others" name="others" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#updateModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id'); // Extract info from data-* attributes
            var health = button.data('health');
            
            var others = button.data('others');

            // Update the modal's content
            var modal = $(this);
            modal.find('#deduction_id').val(id);
            modal.find('#healthinsurance').val(health);
            
            modal.find('#others').val(others);
        });
    });
    </script>

    <script src="javascript/admin_dashboard.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>
