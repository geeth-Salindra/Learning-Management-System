<?php
$host = "localhost";
$user = "root";     
$pass = "";        
$dbname = "LMS";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(" Connection failed: " . $conn->connect_error);
}
?>
