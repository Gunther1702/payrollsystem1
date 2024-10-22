<?php
include('db.php'); // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input
    $type = isset($_POST['type']) ? mysqli_real_escape_string($conn, $_POST['type']) : '';

    // Check if type is not empty and is a valid deduction type
    $validTypes = ['healthinsurance', 'loans', 'others']; // Add all valid types here
    if (in_array($type, $validTypes)) {
        $query = "SELECT $type FROM deductions WHERE deduction_id = 1"; // Assuming you have one row
        $result = mysqli_query($conn, $query);

        if ($result) {
            // Fetch the row and return the value
            $row = mysqli_fetch_assoc($result);
            if ($row) {
                echo $row[$type]; // Output the selected deduction value
            } else {
                echo "No deduction value found.";
            }
        } else {
            echo "Database query error: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid deduction type.";
    }
}

mysqli_close($conn);
?>
