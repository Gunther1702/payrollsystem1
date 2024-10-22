<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication


$employee = null;
if (isset($_GET['employee_id'])) {
    $empId = $_GET['employee_id'];
    $query = "SELECT * FROM accounts WHERE id = '$empId'";
    $result = mysqli_query($conn, $query);
    $employee = mysqli_fetch_assoc($result);
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Fetch employee data from employee table
$result = mysqli_query($conn, "SELECT * FROM employee");
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Fetch deductions data for dropdown
$deductions_result = mysqli_query($conn, "SELECT * FROM deductions");
if (!$deductions_result) {
    die("Database query failed: " . mysqli_error($conn));
}

$deductions = [];
while ($row = mysqli_fetch_assoc($deductions_result)) {
    $deductions[] = $row;
}

// Fetch overtime rate
$overtime_result = mysqli_query($conn, "SELECT * FROM overtime");
if (!$overtime_result) {
    die("Database query failed: " . mysqli_error($conn));
}
$overtime_row = mysqli_fetch_assoc($overtime_result);
$overtime_rate = $overtime_row['rate'];

// Fetch salary rate
$salary_result = mysqli_query($conn, "SELECT * FROM salary");
if (!$salary_result) {
    die("Database query failed: " . mysqli_error($conn));
}
$salary_row = mysqli_fetch_assoc($salary_result);
$salary_rate = $salary_row['salary_rate'];

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
    <title>Admin Dashboard - Payroll Management System</title>
    <link rel="stylesheet" href="css/admin_dashboard1.css">
    <link rel="stylesheet" href="css/menu1.css">
    <link rel="stylesheet" href="css/modal1.css">
    <link rel="stylesheet" href="css/admin1.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <style>
    .modal-header {
    margin-bottom: 10px;
    position: relative; /* Ensure the close button is positioned relative to this */
    padding-right: 40px; /* Add padding to the right for the close button */
}

.modal-title {
    font-size: 1.5em; /* Increase font size for better visibility */
    color: #007bff; /* Change color to match your theme */
}

.modal-close {
    cursor: pointer;
    font-size: 24px; /* Size of the close button */
    color: #6c757d;
    position: absolute; /* Position it absolutely within the header */
    top: -20px; /* Adjust the top position */
    margin-left: 620px; /* Adjust the right position */
}

        /* Modal Form Background */
.modal-form {
    background-color: #e6f7ff; /* Light blue background */
    padding: 10px; /* Inner padding */
    border-radius: 4px; /* Rounded corners */
    margin-top: -18px;
}

        .modal-container input[type="submit"] {
    background: linear-gradient(to bottom, #007BFF, #6F42C1);
    color: white; /* White text */
    border: none; /* No border */
    padding: 10px; /* Inner padding */
    border-radius: 4px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    width: 100%; /* Full width */
    transition: background-color 0.3s; /* Smooth background transition */
}

.employee-info-container {
    display: flex;
    flex-direction: column;
    gap: 10px; /* Space between boxes */
}

.info-box {
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-box p {
    margin: 0; /* Remove default margin */
}

.info-box span {
    font-weight: bold;
    color: #333;
}


    .employee-info-box {
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin-top: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.employee-info-box p {
    margin: 10px 0;
}

.employee-info-box span {
    font-weight: bold;
    color: #333;
}

        .modal-container {
    display: flex;
    justify-content: space-between; /* Align items in a row */
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden;
    width: 700px;
}

.modal-container div {
    padding: 20px;
}

h2 {
    margin-bottom: 10px;
}

        /* Basic Styles for Table */
        .menu-bar a {
            text-decoration: none; /* Remove underline */
            color: inherit; /* Keep the text color */
        }

        .menu-bar a:hover,
        .menu-bar a.active {
            text-decoration: none; /* Ensure no underline on hover and active */
            background: linear-gradient(to right, #1e3c72, #2a69ac);
}

#employeeTable {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    overflow-x: auto; /* Allow horizontal scrolling */
}

/* Table Header and Cells */
#employeeTable th, #employeeTable td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

/* Responsive Styles */
@media (max-width: 768px) {
    #employeeTable, 
    #employeeTable thead, 
    #employeeTable tbody, 
    #employeeTable th, 
    #employeeTable td, 
    #employeeTable tr {
        display: block;
    }

    #employeeTable thead {
        display: none; /* Hide the header on small screens */
    }

    #employeeTable tr {
        margin-bottom: 15px; /* Space between rows */
        border: 1px solid #ddd; /* Border for individual rows */
    }

    #employeeTable td {
        text-align: right; /* Align text to the right */
        position: relative;
        padding-left: 50%; /* Add space for labels */
        border: none; /* Remove borders */
    }

    #employeeTable td:before {
        content: attr(data-label); /* Use data-label for headers */
        position: absolute;
        left: 10px;
        width: calc(50% - 20px); /* Adjust width for labels */
        text-align: left;
        font-weight: bold;
    }
}

        
      #employeeTable thead {
    position: sticky;
    top: 0;
    z-index: 20; /* Increased z-index */
    background: linear-gradient(to bottom, #007BFF, #6F42C1);
}

.scrolled {
    background: white; /* Change background when scrolling */
    color: black; /* Change text color to black for the header */
}

#employeeTable tbody tr td {
    transition: color 0.3s ease; /* Smooth transition for color changes */
}

#employeeTable tbody tr td.scrolled {
    color: black; /* Change color for specific columns when scrolled */
}

        .header {
            background: linear-gradient(to bottom, #007BFF, #6F42C1);
        }

       
    /* Change header background */
    .header-title {
        background: linear-gradient(to bottom, #007BFF, #6F42C1);
        color: white; /* Text color for the header title */
        padding: 10px; /* Add some padding for aesthetics */
    }

        

        
        .menu-btn {
            background-color: #395886;
            color: white;
            border: none; /* No border */
            border-radius: 10px;
            height: 30px;
            width: 30px;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s, box-shadow 0.3s; /* Add transition for box-shadow */
        }

        .menu-btn:hover {
            background-color: black;
        }

        .menu-btn:active {
            box-shadow: 0 0 0 3px white; /* White shadow only on the border */
        }

        
        .alert {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            background-color: #066cfa; 
        }

        .alert.error {
            background-color: #d9534f; /* Red for error */
        }

        .btn-add {
            background: linear-gradient(to bottom, #007BFF, #6F42C1); /* Blue to Purple */
            color: white; 
            padding: 10px 15px;
            border: none; 
            border-radius: 5px;
            cursor: pointer; 
            margin: 0; /* Remove any default margin */
        }

        .btn:hover {
            background-color: #066cfa;
        }

        .btn-edit {
            background: linear-gradient(to bottom, #007BFF, #6F42C1); /* Blue to Purple */

            color: white; 
            padding: 10px 15px;
            border: none; 
            border-radius: 5px;
            cursor: pointer; 
            margin: 0; /* Remove any default margin */
        }

        .btn-update {
            background: linear-gradient(to bottom, #007BFF, #6F42C1); /* Blue to Purple */
            color: white; 
            padding: 10px 15px;
            border: none; 
            border-radius: 5px;
            cursor: pointer; 
            margin: 0; /* Remove any default margin */
        }

        
        .btn-delete{
            background: linear-gradient(to bottom, rgb(254,135,135), rgb(255,0,0) ); /* Blue to Purple */

            color: white;
            border-radius: 8px ;
            padding: 5px 9px;

        }

        .btn.edit:hover, .btn.update:hover, .btn.delete:hover {
            opacity: 0.8; /* Slightly reduce opacity on hover */
        }

        .action-buttons {
            display: flex;
            gap: 10px; /* Adjust the gap between buttons */
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

        <section id="accounts" class="dashboard-section">
            <h2>Employee Records</h2>
            
            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'Error') === false ? '' : 'error'; ?>" id="alertMessage">
                    <strong><?php echo strpos($message, 'Error') === false ? 'Success!' : 'Warning!'; ?></strong>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="action-bar">
                <button class="btn-add" onclick="openModal()">Add New Employee</button>
                <div class="search-container" style="margin-bottom: -90px; margin-right:1px;">
                    <span class="search-label">Search:</span>
                    <input type="text" id="searchBar" placeholder="Search employees...">
                </div>
            </div>
            
                <table id="employeeTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Fullname</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Gender</th>
                            <th>Employee Type</th>
                            <th>Department</th>
                            <th>Address</th>
                            <th class="action-header">
                                <div class="action-header-overlay"></div>
                                <span class="action-header-text">Action</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                    <td>{$row['emp_id']}</td>
                                    <td>{$row['fullname']}</td>
                                    <td>{$row['contact']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['gender']}</td>
                                    <td>{$row['emp_type']}</td>
                                    <td>{$row['department']}</td>
                                    <td>{$row['address']}</td>
                                    <td>
                                        <div class='action-buttons'>
                                            <a href='edit_employee.php?emp_id={$row['emp_id']}' class='btn-edit'>Edit</a>
                                            <a href='javascript:void(0)' class='btn-update' onclick=\"openUpdateModal({$row['emp_id']}, '{$row['fullname']}')\">Update</a>
                                            <a href='delete_employee.php?id={$row['emp_id']}' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this employee?\");'>Delete</a>
                                        </div>
                                    </td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            
        </section>
    </div>

    <!-- Modal for Adding Employee -->
    <div class="modal-overlay" id="addEmployeeModal">
        <div class="modal-container" style="display: flex;">
            <div style="flex: 1; padding: 20px; border-right: 1px solid #ddd;">
                <h2 style="color: #00BFFF;">Employee Info</h2>
                <div class="employee-info-container" style="background-color: #e6f7ff;">
                    <?php if ($employee): ?>
                        <div class="info-box">
                            <p>Full Name: <span><?php echo htmlspecialchars($employee['full_name']); ?></span></p>
                        </div>
                        <div class="info-box">
                            <p>Gender: <span><?php echo htmlspecialchars($employee['gender']); ?></span></p>
                        </div>
                        <div class="info-box">
                            <p>Department: <span><?php echo htmlspecialchars($employee['department']); ?></span></p>
                        </div>
                        <div class="info-box">
                            <p>Employee Type: <span><?php echo htmlspecialchars($employee['employee_type']); ?></span></p>
                        </div>
                        <div class="info-box">
                            <p>Contact: <span><?php echo htmlspecialchars($employee['contact']); ?></span></p>
                        </div>
                        <div class="info-box">
                            <p>Address: <span><?php echo htmlspecialchars($employee['address']); ?></span></p>
                        </div>
                        <div class="info-box">
                            <p>Email: <span><?php echo htmlspecialchars($employee['username']); ?></span></p>
                        </div>
                    <?php else: ?>
                        <p>No employee info found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div style="flex: 1; padding: 20px;">
                <div class="modal-header">
                    <span class="modal-title" style="font-weight: bold; color: #00BFFF;">Add New Employee</span>
                    <span class="modal-close" onclick="closeModal()">&times;</span>
                    <span class="modal-close" onclick="closeModal()" style="position: absolute; right: 10px; top: 10px; font-size: 24px;">&times;</span>
                </div>
                <div class="modal-form"> <!-- Add this div to wrap the form -->
                    <form action="add_employee.php" method="post">
                        <label for="fullname">Full Name:</label>
                        <input type="text" name="fullname" required><br>
                        <label for="gender">Gender:</label>
                        <select name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select><br>
                        <label for="department">Department:</label>
                        <select name="department" required>
                            <option value="Human Resource">Human Resource</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Finance">Finance</option>
                        </select><br>
                        <label for="emp_type">Employee Type:</label>
                        <select name="emp_type" required>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Casual">Casual</option>
                            <option value="Shift Worker">Shift Worker</option>
                        </select><br>
                        <label for="contact">Contact:</label>
                        <input type="text" name="contact" class="input-large" required pattern="\d*" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '');"><br>
                        <label for="address">Address:</label>
                        <input type="text" name="address" required><br>
                        <label for="email">Email:</label>
                        <input type="email" name="email" required><br>
                        <input type="submit" name="submit" value="Add Employee">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Updating Employee -->
    <div class="modal-overlay" id="updateEmployeeModal">
    <div class="modal-container">
        <div class="modal-header">
            <span class="modal-title" id="employeeName">Update Employee</span>
            <span class="modal-close" onclick="closeUpdateModal()">&times;</span>
        </div>
        <form id="updateEmployeeForm" action="update_employee.php" method="post" style="background-color: lightblue; padding: 20px; border-radius: 5px;">
            <input type="hidden" name="emp_id" id="updateEmpId">
            
            <label for="deduction">Deductions:</label>
                <select id="deductionSelect" name="deduction" required onchange="fetchDeductionValue(this.value)">
                    <option value="">Select Deduction</option>
                    <option value="none">None</option>
                    <option value="healthinsurance">Health Insurance</option>
                    <option value="loans">Loans</option>
                    <option value="others">Others</option>
                </select>

                <label for="deductionValue">Deduction Value:</label>
                <input type="number" id="deductionValue" name="deduction_value" required class="input-large" readonly><br>

                <input type="number" id="loanTextInput" name="loan_amount" style="display: none;" placeholder="Enter loan amount" />

            <label for="overtime_hours">Overtime (hours):</label>
            <input type="number" name="overtime_hours" required class="input-large" oninput="validateInput(this); calculateNetPay()" min="0" step="0.01"><br>

            <label for="bonus_amount">Bonus Amount:</label>
            <input type="number" name="bonus_amount" required class="input-large" oninput="validateInput(this); calculateNetPay()" min="0" step="0.01"><br>

            <script>    
            function validateInput(input) {
                // Remove any 'e' or 'E' from the input value
                input.value = input.value.replace(/[eE]/g, '');
            }
            </script>

                <label for="net_pay">Net Pay:</label>
                <input type="number" name="net_pay" id="netPay" required class="input-large" readonly><br>
            <input type="submit" name="update" value="Update"> <br> <br>
        </form>
    </div>
</div>


    <script src="javascript/search.js"></script>
    <script src="javascript/admin_dashboard.js"></script>
    <script>


        function closeModal() {
    document.getElementById('addEmployeeModal').style.display = 'none';
    clearEmployeeInfo();
    resetForm();
}

function clearEmployeeInfo() {
    const employeeInfoContainer = document.querySelector('.employee-info-container');
    employeeInfoContainer.innerHTML = ''; // Clear the content
}

function resetForm() {
    const form = document.querySelector('form');
    form.reset(); // Reset form fields
}

        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('expanded');
        }

        // Function to set active link in sidebar
        function setActive() {
            const links = document.querySelectorAll('.menu-bar a');
            const currentPage = window.location.pathname.split('/').pop(); // Get the current page

            links.forEach(link => {
                if (link.href.includes(currentPage)) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }

        function openModal() {
            document.getElementById('addEmployeeModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('addEmployeeModal').style.display = 'none';
        }

        function openUpdateModal(empId, empName) {
            document.getElementById('employeeName').innerText = empName;
            document.getElementById('updateEmpId').value = empId;
            document.getElementById('updateEmployeeModal').style.display = 'flex';
        }

        function closeUpdateModal() {
            document.getElementById('updateEmployeeModal').style.display = 'none';
            resetUpdateForm(); // Reset the form
        }
        function resetUpdateForm() {
    const form = document.getElementById('updateEmployeeForm');
    form.reset(); // Reset form fields
    document.getElementById('deductionValue').value = ''; // Clear deduction value
    document.getElementById('loanTextInput').style.display = 'none'; // Hide loan input
}


        // Show/hide loan text input based on deduction selection
// Event listener for deduction type selection
// Event listener for deduction selection
document.getElementById('deductionSelect').addEventListener('change', function() {
    const loanTextInput = document.getElementById('loanTextInput');
    if (this.value === 'loans') {
        loanTextInput.style.display = 'block'; // Show loan input
        loanTextInput.value = ''; // Clear previous input
    } else {
        loanTextInput.style.display = 'none'; // Hide loan input
        loanTextInput.value = ''; // Clear input
        fetchDeductionValue(this.value); // Fetch deduction value
    }
});

// Fetch deduction value
function fetchDeductionValue(deductionType) {
    const loanAmount = parseFloat(document.getElementById('loanTextInput').value) || 0;

    // Handle "None" option
    if (deductionType === "none") {
        document.getElementById('deductionValue').value = ''; // Clear deduction value
        calculateNetPay(); // Recalculate net pay with no deductions
        return; // Exit the function
    }

    // Make AJAX request for health insurance and others
    $.ajax({
        url: 'get_deduction_value.php',
        type: 'POST',
        data: { type: deductionType, loanAmount: loanAmount },
        success: function(data) {
            // If the selected deduction type is health insurance or others, display the value
            if (deductionType === "healthinsurance" || deductionType === "others") {
                document.getElementById('deductionValue').value = data; // Update with fetched value
            } else {
                document.getElementById('deductionValue').value = ''; // Clear for other types if necessary
            }
            calculateNetPay(); // Recalculate net pay
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error: " + status + ": " + error);
        }
    });
}



// Function to calculate Net Pay
function calculateNetPay() {
    const empId = parseInt(document.getElementById('updateEmpId').value); 
    console.log("Calculating net pay for Employee ID:", empId);
    
    const deduction = parseFloat(document.getElementById('deductionValue').value) || 0;
    const loanAmount = parseFloat(document.getElementById('loanTextInput').value) || 0; 
    const overtimeHours = parseFloat(document.querySelector('input[name="overtime_hours"]').value) || 0;
    const bonusAmount = parseFloat(document.querySelector('input[name="bonus_amount"]').value) || 0;

    $.ajax({
        url: 'get_salary_overtime.php',
        type: 'GET',
        data: { emp_id: empId }, 
        success: function(data) {
            console.log("AJAX Response:", data); // Log the raw data
            const rates = JSON.parse(data);
            if (rates.error) {
                console.error(rates.error);
                return; 
            }

            const salaryRate = rates.salaryRate || 0; 
            const overtimeRate = rates.overtimeRate || 0;

            console.log("Salary Rate:", salaryRate, "Overtime Rate:", overtimeRate); // Log rates

            // Calculate net pay
            const netPay = salaryRate - deduction - loanAmount + (overtimeHours * overtimeRate) + bonusAmount;
            console.log("Calculated Net Pay:", netPay.toFixed(2)); // Log calculated net pay
            document.getElementById('netPay').value = netPay.toFixed(2); 
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error: " + status + ": " + error);
        }
    });
}



// Ensure this is added to input event listeners
document.querySelector('input[name="overtime_hours"]').addEventListener('input', calculateNetPay);
document.querySelector('input[name="bonus_amount"]').addEventListener('input', calculateNetPay);
document.getElementById('loanTextInput').addEventListener('input', calculateNetPay);
document.getElementById('deductionSelect').addEventListener('change', function() {
    fetchDeductionValue(this.value);
});



        // Function to hide alert message after 5 seconds
        function hideAlert() {
            const alertMessage = document.getElementById('alertMessage');
            if (alertMessage) {
                setTimeout(() => {
                    alertMessage.style.display = 'none';
                }, 5000); // 5 seconds
            }
        }

        // Initialize functionality on page load
        document.addEventListener('DOMContentLoaded', function() {
            setActive(); // Set the active link
            hideAlert(); // Start timer for alert
            
            <?php if ($message): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo $message; ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>
