<?php

$servername = "localhost";
$username = "bdgcassino_db";
$password = "bdgcassino_db";
$dbname = "bdgcassino_db";

// Create connection  
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

