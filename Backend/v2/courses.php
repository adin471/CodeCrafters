<?php
    // set $method as one of the below specified.
    // 1 - > $_GET for testing purposes 
    // 2 - > $_POST for live version.

    $method = $_POST;
    include('main.php');

    if(isset($method['search'])){
        if($method['search'] == ''){
            Search_Courses(null);
        } else {
            Search_Courses($method['search'] ?? null);
        }
    } else {
        Search_Courses(null);
    }
?>