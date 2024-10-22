<?php
// Database connection
$host = 'localhost'; // Database host
$db = 'payrollmanagement'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get data from the request
$username = $_POST['username'] ?? '';
$new_password = $_POST['new_password'] ?? '';

// Validate input
if (empty($username) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit;
}

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update password in the database
$sql = "UPDATE accounts SET password = ? WHERE username = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ss", $hashed_password, $username);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Password reset successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No user found with that email.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating password: ' . $stmt->error]);
}

// Close connections
$stmt->close();
$conn->close();
?>
