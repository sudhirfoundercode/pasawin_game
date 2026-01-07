<?php
$servername = "localhost";
$username = "pasawin_12345";
$password = "pasawin_12345";
$dbname = "pasawin_12345";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo data($conn,1);
function data($conn,$id){
$query=mysqli_fetch_assoc(mysqli_query($conn,"SELECT `longtext` FROM `admin_settings` WHERE id=$id LIMIT 1;"));
return $query['longtext'];
}


 //$conn->close();
?>