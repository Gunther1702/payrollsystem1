<?php
// Database connection parameters
$servername = "localhost"; // Change as needed
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "payrollmanagement"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the employee ID from the request
$employeeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($employeeId > 0) {
    // Prepare the SQL query to fetch employee data
    $stmt = $conn->prepare("
        SELECT e.emp_id, e.fullname, e.gender, e.emp_type, e.department,
               e.deduction, e.overtime, e.bonus, e.contact, e.address,
               e.email, e.net_pay,
               s.full_time_salary, s.part_time_salary, s.casual, s.shift_worker_salary,
               d.healthinsurance, d.others, d.loans
        FROM employee e
        LEFT JOIN salary s ON e.emp_type = CASE 
            WHEN 'Full-time' THEN 'full_time_salary'
            WHEN 'Part-time' THEN 'part_time_salary'
            WHEN 'Casual' THEN 'casual'
            WHEN 'Shift Worker' THEN 'shift_worker_salary'
        END
        LEFT JOIN deductions d ON d.deduction_id = 1
        WHERE e.emp_id = ?
    ");
    $stmt->bind_param("i", $employeeId);
    $stmt->execute();
    
    // Fetch the result
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Output data of the employee
        $employeeData = $result->fetch_assoc();
        echo json_encode($employeeData);
    } else {
        echo json_encode(["error" => "Employee not found."]);
    }
    
    // Close statement
    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid employee ID."]);
}

// Close connection
$conn->close();
?>
