<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Set pagination variables
$limit = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch employee data with pagination
$employeeResult = mysqli_query($conn, "SELECT * FROM employee LIMIT $limit OFFSET $offset");
if (!$employeeResult) {
    die("Database query failed: " . mysqli_error($conn));
}

// Fetch total employee count for pagination
$totalResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM employee");
$totalEmployees = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalEmployees / $limit);

// Fetch current overtime and salary rates
$overtimeResult = mysqli_query($conn, "SELECT * FROM overtime WHERE ot_id = 1");
$salaryResult = mysqli_query($conn, "SELECT * FROM salary WHERE salary_id = 1");
$currentOvertimeRate = mysqli_fetch_assoc($overtimeResult);
$currentSalaryRate = mysqli_fetch_assoc($salaryResult);

// Initialize variables for messages
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
    <title>Payroll Section - Payroll Management System</title>
    <link rel="stylesheet" href="css/admin_dashboard1.css">
    <link rel="stylesheet" href="css/menu1.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
        #searchInput {
    width: 100%; /* Make it take full width of its container */
    max-width: 200px; /* Set a max width */
    padding: 5px; /* Add padding for better usability */
    box-sizing: border-box; /* Include padding in the width calculation */
}

/* Media Query for smaller screens */
@media (max-width: 768px) {
    #searchInput {
        max-width: 150px; /* Adjust max width for smaller screens */
    }
}

          .menu-bar a {
            text-decoration: none; /* Remove underline */
            color: inherit; /* Keep the text color */
        }

        .menu-bar a:hover,
        .menu-bar a.active {
            text-decoration: none; /* Ensure no underline on hover and active */
            background: linear-gradient(to right, #1e3c72, #2a69ac);
}
    .table th {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Remove or comment out the background color to prevent conflicts */
.table-header-gradient {
    background: linear-gradient(to bottom, #007BFF, #6F42C1);
    color: white;
    font-weight: bold;
}


        
        .btn-group{
            border-radius: 12px;
        }
        .btn-primary ml-3 {
            border-radius: 12px;
        }
        .table tr {
            font-size: 17px;
        }
        .menu-btn {
            background-color: #395886;
            color: white;
            border: black;
            border-radius: 10px;
            height: 30px;
            width: 30px;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .menu-btn:hover {
            background-color: black;
        }

        .main-content header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 5px;
        }

        .header-title h1 {
            font-size: 32px;
            margin: 10px 0;
        }

        

        .pagination {
    display: flex; /* Use flexbox for alignment */
    justify-content: center; /* Center the pagination */
    margin: 20px 0; /* Space around the pagination */
}

.pagination span {
    
    color: black; /* Text color for page numbers */
    margin: 0 5px; /* Margin between spans */
    padding: 10px 15px; /* Padding for clickable area */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s; /* Smooth transition for hover */
}

.pagination span.active {
   
    color: white; /* Text color for active page */
    border: 2px solid gray; /* Box around active page number */
}

.pagination span:hover {
    background-color: black; /* Hover effect for page numbers */
}


.pagination button {
    background-color: white;
    color: black;
    border: none; /* Remove border */
    border-radius: 5px;
    height: 50px; /* Increased height for better click area */
    width: auto; /* Let width be determined by content */
    padding: 10px 15px; /* Padding for comfortable click area */
    font-size: 16px; /* Adjust font size if needed */
    cursor: pointer;
    transition: background-color 0.3s;
}

.pagination button:hover:not(:disabled) {
    background-color: #333333;
}

.pagination button:disabled {
    background-color: white;
    cursor: not-allowed;
}

.pagination span {
    background-color: white;
    margin: 0 5px; /* Margin between spans */
    padding: 10px 15px; /* Ensure padding for clickable area */
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.pagination span.active {
    background-color: white;
    color: black;
}

.pagination span:hover {
    background-color: #f0f0f0; /* Hover effect for spans */
}

 
        h2 {
            margin: 20px 0;
        }

        .table-container {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .total-box {
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #F5F5F5;
            width: 100%;
            box-sizing: border-box;
        }

        .total-text {
            font-weight: bold;
            text-align: right;
            display: block;
        }
        
        .alert {
            background-color: #066cfa;
            color: white;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
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

        <h2>Payroll Section</h2>

        <?php if ($message): ?>
            <div class="alert alert-success" id="notificationBanner" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="action-bar mb-3 d-flex justify-content-between align-items-center">
            <div class="btn-group" role="group" aria-label="Update Buttons">
                <button type="button" class="btn btn-primary" style="background: linear-gradient(to bottom, #007BFF, #6F42C1); /* Blue to Purple */" data-toggle="modal" data-target="#updateOvertimeModal">Update Overtime Rate</button>
                <button type="button" class="btn btn-primary ml-3" style="background: linear-gradient(to bottom, #007BFF, #6F42C1); /* Blue to Purple */"data-toggle="modal" data-target="#updateSalaryModal">Update Salary Rate</button>
            </div>
            <div class="d-flex justify-content-end align-items-center mb-3" style="margin-right: 15px;">
                <div class="rate-box" style="margin-left: 10px; margin-top: 10px;">
                    <div class="p-3" style="border: 1px solid #ccc; border-radius: 5px; background: linear-gradient(to bottom, #007BFF, #6F42C1); color: white; height: 40px; display: flex; align-items: center;">
                        <strong style="font-size: 14px;">Overtime Rate (Per hour):</strong>
                        <span class="ml-2" style="font-size: 14px;">₱ <?php echo $currentOvertimeRate['rate']; ?></span>
                    </div>
                </div>

                <div class="rate-box" style="margin-left: 10px; margin-top: 10px;">
    <div class="p-3" style="border: 1px solid #ccc; border-radius: 5px; background: linear-gradient(to bottom, #007BFF, #6F42C1); color: white; height: 70px; display: flex; flex-direction: column; width: 300px; padding: 10px; font-size: 10px; margin-right: 70px;">
        <strong style="font-size: 12px; text-align: center; margin-top: -12px;">Salary Rates:</strong>

        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
            <div style="flex: 1; text-align: left;">
                <strong>Full Time:</strong>
                <span>₱ <?php echo $currentSalaryRate['full_time_salary']; ?></span>
            </div>
            <div style="flex: 1; text-align: right;">
                <strong>Shift Worker:</strong>
                <span>₱ <?php echo $currentSalaryRate['shift_worker']; ?></span>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
            <div style="flex: 1; text-align: left;">
                <strong>Part Time:</strong>
                <span>₱ <?php echo $currentSalaryRate['part_time_salary']; ?></span>
            </div>
            <div style="flex: 1; text-align: right;">
                <strong>Casual:</strong>
                <span>₱ <?php echo $currentSalaryRate['casual']; ?></span>
            </div>
        </div>
    </div>
</div>



            </div>
        </div>

        <div class="table-container" style="margin-top:-50px; margin-bottom: 5px; overflow: hidden;">
            <div class="d-flex align-items-center">
                <label for="entries" class="mr-2" style= "font-size: 15px; margin-top: 5px; margin-bottom: -20px;">Show Entries:</label>
                <select id="entries" class="form-control" style="width: 50px; padding: 2px 0; height: 30px; margin-bottom: -30px;" onchange="changeEntries()">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="25" <?php if ($limit == 25) echo 'selected'; ?>>25</option>
                    <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                </select>

                <div class="d-flex justify-content-end align-items-center mb-0" style="margin-left:900px; margin-bottom: -10px;">
                    <label for="searchInput" class="mr-2" style="margin-top: 35px">Search:</label>
                    <input type="text" id="searchInput" placeholder="Search employees..." class="form-control" style="width: 200px; height: 30px; margin-bottom: -24px;">
                </div>
            </div>
        </div>

            <table id="employeeTable" class="table">
                <thead class="table-header-gradient">
                    <tr >
                        <th>Employee ID</th>
                        <th>Fullname</th>
                        <th>Overtime Rate</th>
                        <th>Bonus</th>
                        <th>Deduction</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($employeeResult)) {
                        echo "<tr>
                                <td>{$row['emp_id']}</td>
                                <td>{$row['fullname']}</td>
                                <td>{$row['overtime']} hrs</td>
                                <td>{$row['bonus']}</td>
                                <td>{$row['deduction']}</td>
                                <td>₱ {$row['net_pay']}</td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>



        <div class="pagination" style="margin-left: 1050px; margin-top: -20px;">
            <button <?php if ($page <= 1) echo 'disabled'; ?> onclick="location.href='?page=<?php echo max(1, $page - 1); ?>&entries=<?php echo $limit; ?>'">Previous</button>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <span class="page-number <?php if ($i == $page) echo 'active'; ?>" onclick="location.href='?page=<?php echo $i; ?>&entries=<?php echo $limit; ?>'"><?php echo $i; ?></span>
            <?php endfor; ?>
            <button <?php if ($page >= $totalPages) echo 'disabled'; ?> onclick="location.href='?page=<?php echo min($totalPages, $page + 1); ?>&entries=<?php echo $limit; ?>'">Next</button>
        </div>

        <!-- Update Overtime Rate Modal -->
        <div class="modal fade" id="updateOvertimeModal" tabindex="-1" aria-labelledby="updateOvertimeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="background-color: lightblue;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateOvertimeModalLabel">Update Overtime Rate</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="update_overtime.php">
                            <div class="form-group">
                                <label for="overtimeRate">Overtime Rate</label>
                                <input type="number" class="form-control" id="overtimeRate" name="overtime_rate" value="<?php echo $currentOvertimeRate['rate']; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Overtime Rate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Salary Rate Modal -->
        <!-- Update Salary Rate Modal -->
        <div class="modal fade" id="updateSalaryModal" tabindex="-1" aria-labelledby="updateSalaryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: lightblue;">
                        <h5 class="modal-title" id="updateSalaryModalLabel">Update Salary Rates</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="background-color: lightblue;">
                        <form method="POST" action="update_salary.php">
                            <div class="form-group">
                                <label for="fullTimeSalary">Full Time Salary</label>
                                <input type="number" class="form-control" id="fullTimeSalary" name="full_time_salary" placeholder="Enter full-time salary" value="<?php echo $currentSalaryRate['full_time_salary']; ?>" required min="0">
                                <small class="form-text text-muted">Salary Rate.</small>
                            </div>
                            <div class="form-group">
                                <label for="partTimeSalary">Part Time Salary</label>
                                <input type="number" class="form-control" id="partTimeSalary" name="part_time_salary" placeholder="Enter part-time salary" value="<?php echo $currentSalaryRate['part_time_salary']; ?>" required min="0">
                                <small class="form-text text-muted">Salary Rate.</small>
                            </div>
                            <div class="form-group">
                                <label for="casualSalary">Casual Salary</label>
                                <input type="number" class="form-control" id="casualSalary" name="casual_salary" placeholder="Enter casual worker salary" value="<?php echo $currentSalaryRate['casual']; ?>" required min="0">
                                <small class="form-text text-muted">Salary Rate.</small>
                            </div>
                            <div class="form-group">
                                <label for="shiftWorkerSalary">Shift Worker Salary</label>
                                <input type="number" class="form-control" id="shiftWorkerSalary" name="shift_worker" placeholder="Enter shift worker salary" value="<?php echo $currentSalaryRate['shift_worker']; ?>" required min="0">
                                <small class="form-text text-muted">Salary Rate.</small>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">Update Salary Rates</button>
                        </form>
                        <div class="updated-salary-container mt-3">
                            <h6>Current Salary Rates:</h6>
                            <div class="p-2" style="border: 1px solid #ccc; border-radius: 5px;">
                                <strong>Full Time:</strong> ₱ <?php echo $currentSalaryRate['full_time_salary']; ?><br>
                                <strong>Part Time:</strong> ₱ <?php echo $currentSalaryRate['part_time_salary']; ?><br>
                                <strong>Casual:</strong> ₱ <?php echo $currentSalaryRate['casual']; ?><br>
                                <strong>Shift Worker:</strong> ₱ <?php echo $currentSalaryRate['shift_worker']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <script>
            $(document).ready(function() {
                $('#employeeTable').DataTable({
                    paging: false,
                    ordering: true,
                    order: [[0, 'asc']],
                    searching: false // Disable DataTables search bar
                });

                $('#searchInput').on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $('#employeeTable tbody tr').filter(function() {
                        $(this).toggle(
                            $(this).find('td:nth-child(1)').text().toLowerCase().indexOf(value) > -1 ||
                            $(this).find('td:nth-child(2)').text().toLowerCase().indexOf(value) > -1 ||
                            $(this).find('td:nth-child(3)').text().toLowerCase().indexOf(value) > -1 ||
                            $(this).find('td:nth-child(4)').text().toLowerCase().indexOf(value) > -1 ||
                            $(this).find('td:nth-child(5)').text().toLowerCase().indexOf(value) > -1 ||
                            $(this).find('td:nth-child(6)').text().toLowerCase().indexOf(value) > -1 
                        );
                    });
                });

                if ($('#notificationBanner').length) {
                    setTimeout(function() {
                        $('#notificationBanner').fadeOut();
                    }, 5000);
                }
            });

            function changeEntries() {
                const entries = document.getElementById('entries').value;
                window.location.href = '?page=1&entries=' + entries; // Reset to page 1 when entries change
            }

            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                sidebar.classList.toggle('open');
                mainContent.classList.toggle('expanded');
            }
        </script>

        <script src="javascript/admin_dashboard.js"></script>
    </body>
</html>

<?php
mysqli_close($conn);
?>
