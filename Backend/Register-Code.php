<?php 
    include('Functions-Database.php');

    if(isset($_GET['Venue'])){
        Add_Code_For_Venue($_GET['Venue']);
    } else {
        Array_To_JSON(array(
            "Success" => FALSE,
            "Message" => "Failed to Register Code - Missing URL Parameters",
            "Venue" => NULL
        ));
    }
?>