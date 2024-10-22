<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = mysqli_connect("localhost", "root", "", "payrollmanagement");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are set
    $role = $_POST['role'] ?? null;
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $fullName = $_POST['full_name'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $department = $_POST['department'] ?? null;
    $contact = $_POST['contact'] ?? null;
    $address = $_POST['address'] ?? null;

    // Validate required fields
    if (!$role || !$username || !$password || !$fullName || !$gender || !$department || !$contact || !$address) {
        echo "Error: All fields are required.";
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username is already taken
    $checkStmt = $conn->prepare("SELECT username FROM accounts WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    // Check if the role is admin and if an admin account already exists
    if ($role === 'admin') {
        $adminCheckStmt = $conn->prepare("SELECT COUNT(*) FROM accounts WHERE role = 'admin'");
        $adminCheckStmt->execute();
        $adminCheckStmt->bind_result($adminCount);
        $adminCheckStmt->fetch();

        if ($adminCount > 0) {
            echo "Error: An admin account already exists.";
            $adminCheckStmt->close();
            $checkStmt->close();
            $conn->close();
            exit;
        }
        $adminCheckStmt->close();
    }

    // Check for special characters in username if role is admin
    if ($role === 'admin' && preg_match('/[^a-zA-Z0-9]/', $username)) {
        echo "Error: Admin username cannot contain special characters.";
        $checkStmt->close();
        $conn->close();
        exit;    
    }

    // If the username already exists
    if ($checkStmt->num_rows > 0) {
        echo "Error: This username/email is already registered.";
    } else {
        // Prepare the INSERT statement
        $stmt = $conn->prepare("INSERT INTO accounts (role, username, password, full_name, gender, department, contact, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $role, $username, $hashedPassword, $fullName, $gender, $department, $contact, $address);
        
        // Execute the statement and check for success
        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    $checkStmt->close();
}

$conn->close();
?>
