<?php
    // set $method as one of the below specified.
    // 1 - > $_GET for testing purposes 
    // 2 - > $_POST for live version.
    
    $method = $_GET;
    include('main.php');

    // make sure they are logged in.
    if(!isset($_SESSION['user_id'])){
        Generate_ResponseJSON(FALSE, 'You must be logged in to access this endpoint', null);
        die();
    }

    // check for which action the admin wants to do
    if(isset($method['action'])){
        if($method['action'] == 'refreshaccesscode'){
            Refresh_Access_Code();
        } elseif($method['action'] == 'update_course'){

            $course_name = 'Keep';
            $course_desc = 'Keep';
            $venue_id = 'Keep';
            $course_start = 'Keep';
            $course_end = 'Keep';

            // Check for course Entry ID (PK) \\
            if(!isset($method['id'])){
                Generate_ResponseJSON(FALSE, 'ERROR - Missing Course Entry ID', null);
                die(); 
            }

            // Check for new course name \\
            if(isset($method['coursename'])){
                $course_name = $method['coursename'];
            }

            // Check for new course description \\
            if(isset($method['coursedesc'])){
                $course_desc = $method['coursename'];
            }

            // Check for new venue id \\
            if(isset($method['coursevenue'])){
                $venue_id = $method['coursevenue'];
            }

            // Check for new course start \\
            if(isset($method['coursestart'])){

                // Make sure its numeric, conver to date time and set \\
                if(is_numeric($method['coursestart'])){
                    $course_start = date("Y-m-d H:i:s", $epoch);
                }

            }

            // Check for new course end \\
            if(isset($method['courseend'])){

                // Make sure its numeric, conver to date time and set \\
                if(is_numeric($method['courseend'])){
                    $course_end = date("Y-m-d H:i:s", $epoch);
                }
            }

            // Update_Course($method['id'], $course_name, $course_desc, $venue_id, $course_start, $course_end);
        } elseif($method['action'] == 'add_course'){
            // Check for course Entry ID (PK) \\

            $course_name = 'None';
            $course_desc = 'None';
            $venue_id = 'None';
            $course_start = 'None';
            $course_end = 'None';

            // Add_Course();
        } elseif($method['action'] == 'delete_course'){
            // Check for course Entry ID (PK) \\
            if(!isset($method['id'])){
                Generate_ResponseJSON(FALSE, 'ERROR - Missing Course Entry ID', null);
                die(); 
            }

            Delete_Course($method['id']);
        } elseif($method['action'] == 'delete_user'){
            // Check for course Entry ID (PK) \\
            if(!isset($method['id'])){
                Generate_ResponseJSON(FALSE, 'ERROR - Missing User Account ID', null);
                die(); 
            }

            Delete_User($method['id']);

            //delete all user accounts who arent admin
        } elseif($method['action'] == 'delete_all_users'){
            Delete_All_Users();

            //return access code
        } elseif ($method['action'] == 'return_access_code') {
            if ($_SESSION['user_level'] >= 1) {
                list($success, $result) = Return_Access_Code();
                if ($success) {
                    $code_data = $result->fetch_assoc();
                    Generate_ResponseJSON(TRUE, 'SUCCESS - Access code retrieved successfully', $code_data['code']);
                } else {
                    Generate_ResponseJSON(FALSE, 'ERROR - Failed to retrieve access code', $result);
                }
            } else {
                Generate_ResponseJSON(FALSE, 'ERROR - Admin access required', null);
            }
        }
    } else {
        Generate_ResponseJSON(FALSE, 'ERROR - Invalid parameters', null);
        die();  
    }
?>





