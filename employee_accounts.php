<?php
session_start();
include('db.php'); // Include database connection
include('auth.php'); // Check authentication

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Set pagination variables
$limit = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch employee account data with pagination, excluding admin account
$adminUsername = 'Marco'; // Admin username to exclude
$result = mysqli_query($conn, "SELECT * FROM accounts WHERE username != '$adminUsername' LIMIT $limit OFFSET $offset");
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Fetch total employee count for pagination, excluding admin account
$totalResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM accounts WHERE username != '$adminUsername'");
$totalEmployees = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalEmployees / $limit);

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
    <title>Employee Accounts - Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin_dashboard1.css">
    <link rel="stylesheet" href="css/menu1.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
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
        .pagination span.active {
    background-color: gray; /* Active page color */
    color: white; /* Text color for active page */
    border: 2px solid gray; /* Border for active page number */
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
.custom-button {
    background: linear-gradient(to bottom, #007BFF, #6F42C1); /* Blue to Purple */
    color: white; 
    
    margin-right: 10px;
    border: none; 
    border-radius: 5px;
    cursor: pointer; 
    
    width: 80px;
    height: 40px;
}

        .header-title {
    padding: 20px 0; /* Adjust vertical padding */
}
        .main-content h1 {
    text-align: center; /* Center the header title */
    font-size: 35px;
   
   
}
.main-content h1 {
    text-align: center; /* Center the header title */
    
    height: 10px;
    margin-bottom: -20px;
}
.header-title {
    display: flex;
    justify-content: center; /* Center the content horizontally */
    align-items: center; /* Center the content vertically */
    width: 100%;
    margin: 0; /* Remove any margin */
}


#searchInput {
    width: 100%;
    max-width: 200px;
    padding: 5px;
    box-sizing: border-box;
    margin-top: 20px;
}
header {
    display: flex;
    justify-content: space-between; /* Keeps the button on one side and the title centered */
    align-items: center; /* Vertically center the items */
}

.menu-btn {
    background-color: #395886;
    color: white;
    border: none;
    border-radius: 10px;
    height: 30px;
    width: 30px;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s, box-shadow 0.3s;
    margin: 0; /* Reset margin to prevent misalignment */
}

.menu-btn:hover {
    background-color: black;
}

.menu-btn:active {
    box-shadow: 0 0 0 3px white;
}


.menu-bar a {
    text-decoration: none;
    color: inherit;
}

.menu-bar a:hover,
.menu-bar a.active {
    background: linear-gradient(to right, #1e3c72, #2a69ac);
}

.table th {
    position: sticky;
    top: 0;
    z-index: 10;
}

.table-header-gradient {
    background: linear-gradient(to bottom, #007BFF, #6F42C1);
    color: white;
    font-weight: bold;
    position: sticky; /* Make the header sticky */
    top: 0;          /* Stick to the top */
    z-index: 1;     /* Ensure it stays above other elements */
}

.pagination {
    display: flex; /* Use flexbox for alignment */
    justify-content: center; /* Center the pagination */
    margin: 20px 0; /* Space around the pagination */
    margin-top: -10px;
}

.pagination span {
    color: darkgray; /* Changed to dark gray */
    margin: 0 5px; /* Margin between spans */
    padding: 10px 15px; /* Padding for clickable area */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s, border-color 0.3s; /* Smooth transition for hover */
}


.pagination span.active {
    background-color: gray; /* Active page color */
    color: white; /* Text color for active page */
    border: 2px solid gray; /* Border for active page number */
}
.pagination span:hover {
    background-color: #555; /* Dark gray on hover */
    color: white; /* Text color on hover */
    border-color: white; /* Change border color on hover */
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
    background-color: #555; /* Dark gray on hover */
    color: white; /* Text color on hover */
}

.pagination button:disabled {
    background-color: #555; /* Dark gray on hover */
    cursor: not-allowed;
}

.alert {
    background-color: #066cfa;
    color: white;
}

/* Sidebar active styles */
.sidebar.active {
    display: none; /* Hide sidebar when active */
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
            <div class="header-title" style="margin-top: -20px; margin-bottom: 40px; font-size: 10px;">
                <h1>Payroll Management System</h1>
            </div>
        </header>

        <?php if ($message): ?>
    <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger'; ?>" id="alertMessage">
        <strong><?php echo strpos($message, 'Error') === false ? 'Success!' : 'Warning!'; ?></strong>
        <?php echo $message; ?>
    </div>

    <script>
        // Set a timer to dismiss the alert after 5 seconds (5000 milliseconds)
        setTimeout(function() {
            var alert = document.getElementById('alertMessage');
            if (alert) {
                alert.style.display = 'none'; // Hide the alert
            }
        }, 5000); // Adjust the time (in milliseconds) as needed
    </script>
<?php endif; ?>


        <div class="textheader" style="margin-top: 5px; padding: 30px; margin-left: -1000px; margin-bottom: 20px;">
            <h1>
                Employee Accounts
            </h1>
        </div>

        <div class="action-bar mb-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <label for="entries" class="mr-2" style="margin-top: 20px;">Show Entries:</label>
                <select id="entries" class="form-control" style="width: 100px; margin-top: 10px; height: 30px; font-size:13px; width: 70px;" onchange="changeEntries()">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="25" <?php if ($limit == 25) echo 'selected'; ?>>25</option>
                    <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                </select>
            </div>
            <div class="d-flex align-items-center">
                <label for="searchInput" class="mr-2" style="margin-top: 24px;">Search:</label>
                <input type="text" id="searchInput" placeholder="Search employees..." class="form-control" style="width: 200px;">
            </div>
        </div>

        <table id="employeeAccountsTable" class="table">
            <thead class="table-header-gradient">
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Employee Type</th>
                    <th>Department</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Username</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['gender']; ?></td>
                        <td><?php echo $row['employee_type']; ?></td>
                        <td><?php echo $row['department']; ?></td>
                        <td><?php echo $row['contact']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td>
                            <button class="btn btn-primary custom-button" onclick="location.href='admin_dashboard.php?employee_id=<?php echo $row['id']; ?>'">Add</button>
                            <form action="delete_employee_acc.php" method="POST" style="display:inline;">
                                <input type="hidden" name="employee_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger custom-button" style="background: linear-gradient(to bottom, rgb(254,135,135), rgb(255,0,0));" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                    
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="pagination">
            <button style="background-color: white; color: black;" <?php if ($page <= 1) echo 'disabled'; ?> onclick="location.href='?page=<?php echo max(1, $page - 1); ?>&entries=<?php echo $limit; ?>'">Previous</button>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <span class="page-number <?php if ($i == $page) echo 'active'; ?>" onclick="location.href='?page=<?php echo $i; ?>&entries=<?php echo $limit; ?>'"><?php echo $i; ?></span>
            <?php endfor; ?>
            <button style="padding: 10px 15px; border-radius: 5px; background-color: white; color: black; border: none; margin-right: -800px;" <?php if ($page >= $totalPages) echo 'disabled style="background-color: #cccccc; cursor: not-allowed;"'; ?> onclick="location.href='?page=<?php echo min($totalPages, $page + 1); ?>&entries=<?php echo $limit; ?>'">Next</button>
        </div>


        <script>
            $(document).ready(function() {
                $('#employeeAccountsTable').DataTable({
                    paging: false,
                    ordering: true,
                    order: [[0, 'asc']],
                    searching: false
                });

                $('#searchInput').on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $('#employeeAccountsTable tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                });
            });

            function changeEntries() {
                const limit = document.getElementById('entries').value;
                window.location.href = `?page=1&entries=${limit}`;
            }

            function toggleSidebar() {
                document.getElementById('sidebar').classList.toggle('active');
                document.getElementById('mainContent').classList.toggle('active');
            }

        </script>
        <script src="javascript/admin_dashboard.js"></script>
    </div>
</body>
</html>
