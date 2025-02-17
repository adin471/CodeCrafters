<?php 
    include('Core.php');

    $HWID = '';

    if(isset($_GET["Firstname"]) && isset($_GET["Surname"]) && isset($_GET["Code"])){
        if(isset($_GET["HWID"])){
            $HWID = $_GET["HWID"];
        }
        Register_Attendance($_GET["Firstname"], $_GET["Surname"], $_GET["Code"], $HWID);
    } else {
        Array_To_JSON(array(
            "Success" => FALSE,
            "Message" => "Failed to Register Attendance - Missing URL Parameters",
            "Venue" => NULL
        ));
        die();
    }
?>
