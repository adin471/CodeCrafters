<?php
    // set $method as one of the below specified.
    // 1 - > $_GET for testing purposes 
    // 2 - > $_POST for live version.

    $method = $_GET;
    include('main.php');

    // check if username and password set - > continue script, otherwise - > return invalid param response
    if(isset($method['username']) and isset($method['password'])){

        // check for secret user and password - > create staff account, otherwise - > create user account
        // staff condition: secret user and password invalid - > return error
        if(isset($method['secretu']) and isset($method['secretp'])){
            Register_Account_Staff($method['username'], $method['password'], $method['secretu'], $method['secretp']);
        } else {
            Register_Account_User($method['username'], $method['password']);
        }

    } else {
        Generate_ResponseJSON(FALSE, 'ERROR - Invalid parameters', null);
        die();
    }
?>
