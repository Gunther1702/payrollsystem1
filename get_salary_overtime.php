<?php
include('db.php');

if (isset($_GET['emp_id'])) {
    $emp_id = $_GET['emp_id'];

    // Fetch employee details, including emp_type
    $employee_query = "SELECT emp_type FROM employee WHERE emp_id = ?";
    $stmt = $conn->prepare($employee_query);

    if ($stmt) {
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $employee_result = $stmt->get_result();

        if ($employee_result->num_rows > 0) {
            $employee = $employee_result->fetch_assoc();
            $emp_type = $employee['emp_type'];

            error_log("Employee Type: " . $emp_type); // Log employee type

            // Now fetch the corresponding salary based on emp_type
            $salary_query = "SELECT full_time_salary, part_time_salary, casual, shift_worker FROM salary"; 
            $salary_result = $conn->query($salary_query);

            if ($salary_result) {
                $salary_row = $salary_result->fetch_assoc();
                error_log("Fetched Salary Data: " . print_r($salary_row, true)); // Log salary data
                
                $salary = 0;
                switch ($emp_type) {
                    case 'Full-time':
                        $salary = $salary_row['full_time_salary'];
                        break;
                    case 'Part-time':
                        $salary = $salary_row['part_time_salary'];
                        break;
                    case 'Casual':
                        $salary = $salary_row['casual'];
                        break;
                    case 'Shift Worker': // Ensure this matches exactly
                        $salary = $salary_row['shift_worker'];
                        break;
                    default:
                        echo json_encode(['error' => 'Invalid employee type: ' . $emp_type]);
                        exit;
                }

                // Fetch overtime rate
                $overtime_query = "SELECT rate FROM overtime LIMIT 1"; 
                $overtime_result = $conn->query($overtime_query);
                $overtime_rate = 0;

                if ($overtime_result) {
                    $overtime_row = $overtime_result->fetch_assoc();
                    $overtime_rate = $overtime_row['rate'];
                }

                // Prepare response
                echo json_encode([
                    'salaryRate' => $salary,
                    'overtimeRate' => $overtime_rate,
                    'deduction' => 0, // Adjust as necessary
                    'bonus' => 0      // Adjust as necessary
                ]);
            } else {
                echo json_encode(['error' => 'Salary data not found.']);
            }
        } else {
            echo json_encode(['error' => 'Employee not found.']);
        }
    } else {
        echo json_encode(['error' => 'Database query failed.']);
    }
} else {
    echo json_encode(['error' => 'No employee ID provided.']);
}
?>
