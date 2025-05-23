<?php
$conn = new mysqli("localhost", "root", "", "clickupdated");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
