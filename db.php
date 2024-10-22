<?php
$conn = mysqli_connect('localhost', 'root', '', 'payrollmanagement');
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
