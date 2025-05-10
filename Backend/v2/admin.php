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
             // Refresh Access Code \\
            Refresh_Access_Code();
        } elseif($method['action'] == 'update_course'){

            $course_id = null;
            $course_name = 'Keep';
            $course_desc = 'Keep';
            $venue_id = 'Keep';
            $course_start = 'Keep';
            $course_end = 'Keep';

            // Check for course Entry ID (PK), Also Validate \\
            if(!isset($method['id'])){
                Generate_ResponseJSON(FALSE, 'ERROR - Missing Course Entry ID', null);
                die(); 
            } elseif(is_numeric($method['id'])){
                $course_id = $method['id'];
            } else {
                Generate_ResponseJSON(FALSE, 'ERROR - Invalid Course Entry ID', null);
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
                    $course_start = date("Y-m-d H:i:s", $method['coursestart']);
                } else {
                    $course_start = 'None';
                }

            }

            // Check for new course end \\
            if(isset($method['courseend'])){

                // Make sure its numeric, conver to date time and set \\
                if(is_numeric($method['courseend'])){
                    $course_end = date("Y-m-d H:i:s", $method['courseend']);
                } else {
                    $course_end = 'None';
                }
            }
            
            if($course_name == 'Keep' && $course_desc == 'Keep' && $venue_id == 'Keep' && $course_start == 'Keep' && $course_end == 'Keep'){
                Generate_ResponseJSON(FALSE, 'ERROR - This edit provides no changes, so none were made', null);
                die(); 
            }

            Update_Course($course_id, $course_name, $course_desc, $venue_id, $course_start, $course_end);
        } elseif($method['action'] == 'add_course'){
            
            $course_name = 'None';
            $course_desc = 'None';
            $venue_id = 'None';
            $course_start = 'None';
            $course_end = 'None';

            // Check for course name \\
            if(isset($method['coursename'])){
                $course_name = $method['coursename'];
            }

            // Check for course description \\
            if(isset($method['coursedesc'])){
                $course_desc = $method['coursename'];
            }

            // Check for venue id \\
            if(isset($method['coursevenue'])){
                $venue_id = $method['coursevenue'];
            }

            // Check for course start \\
            if(isset($method['coursestart'])){

                // Make sure its numeric, conver to date time and set \\
                if(is_numeric($method['coursestart'])){
                    $course_start = date("Y-m-d H:i:s", $method['coursestart']);
                } else {
                    $course_start = 'None';
                }

            }

            // Check for new course end \\
            if(isset($method['courseend'])){

                // Make sure its numeric, conver to date time and set \\
                if(is_numeric($method['courseend'])){
                    $course_end = date("Y-m-d H:i:s", $method['courseend']);
                } else {
                    $course_end = 'None';
                }
            }

            if($course_name != 'None' && $course_desc != 'None' && $venue_id !=  'None' && $course_start !=  'None'  && $course_end !=  'None'){
                Add_Course($course_name, $course_desc, $venue_id, $course_start, $course_end);
            }
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
        } elseif($method['action'] == 'delete_all_users'){
            // Delete all non-admin Users \\
            Delete_All_Users();
        } elseif ($method['action'] == 'return_access_code'){
            // Return Access Code \\
            Get_Access_Code();
        } elseif($method['action'] == 'return_venues'){
            // Return Venue Data \\
            Get_Venues();
        } elseif($method['action'] == 'return_users'){
            // Return Users Data \\
            Get_Users();
        }
    } else {
        Generate_ResponseJSON(FALSE, 'ERROR - Invalid parameters', null);
        die();  
    }
?>