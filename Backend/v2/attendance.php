<?php
    // set $method as one of the below specified.
    // 1 - > $_GET for testing purposes 
    // 2 - > $_POST for live version.

    $method = $_GET;
    include('main.php');

    // check if username and password set - > continue script, otherwise - > return invalid param response
    if(isset($method['code'])){
        Register_Attendance($method['code']);
    } else {
        Generate_ResponseJSON(FALSE, 'ERROR - Invalid parameters', null);
        die();
    }
?>