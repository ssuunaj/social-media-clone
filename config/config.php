<?php 
    ob_start();
    session_start();
    $timezone = date_default_timezone_set("Africa/Kampala");

    $conn = mysqli_connect("localhost", "root", "", "social");

    if(mysqli_connect_errno($conn)){
       echo "Failed to connect".mysqli_connect_errno($conn);
    }
?>