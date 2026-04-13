<?php
$conn = new mysqli("localhost", "root", "", "swaply");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>