<?php

    /*
       RAW PHP CODE
    */

    // disable error reporting
    error_reporting(0);

    if (session_status() === PHP_SESSION_NONE) {
     session_start();
    }

    /*                        
       LOCAL FUNCTION SECTION
    */                        

    // Establish Connection to MYSQL Database.
    function Connect_To_Database(){
        $mysqli = mysqli_connect("localhost", "2378946", "kr8n8s", "db2378946");
        if ($mysqli -> connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
            exit();
        }
        return $mysqli;
    }

    // Query Generation Function (Query [STRING], Args [ARRAY...])
    function Generate_Query($Query, ...$Args){
        $mysqli = Connect_To_Database();

        $prepare = $mysqli->prepare($Query);

        $bind_string = '';
        $bind_args = [];

        foreach($Args as $Array){
            $bind_string .= $Array[0];
            $bind_args[] = $Array[1];
        }

        if(empty($bind_string) and empty($bind_args)){
            $result = $mysqli->query($Query);
            $execute = ($result !== false);
            return [$execute, $result];
        } else {
            $bind = $prepare->bind_param($bind_string, ...$bind_args);
        }

        $execute = $prepare->execute();
        $result = $prepare->get_result();

        $mysqli->close();
        return [$execute, $result];
    }

    // JSON / API Response Generation Function (Success [TRUE/FALSE], Message [STRING], Data [...])
    function Generate_ResponseJSON($Success, $Message, $Data){
        // Basic format for API Endpoint response \\
        echo(json_encode([
            "Success" => $Success,
            "Message" => $Message,
            "Data" => $Data
        ]));
    }

    // Code Generation Function (6 characters, lowercase)
    function Generate_Code(){
        // blank variable, this will be used to store the code
        $code = '';

        // Generate 6 random characters
        for($i = 0; $i < 6; $i++){
            $code = ($code . chr(random_int(97, 122)));
        }

        // Return Generated Code
        return $code;
    }

    // Return Access Code Function
    function Return_Access_Code(){
        // Make the query \\
        $query = "SELECT * FROM ODCodesDB WHERE code_id = 1";
        list($execute_success, $execute_result) = Generate_Query($query);

        // Check for query execution success - return false, null if not successful or true and data if successful \\
        if($execute_success == FALSE){
            return [FALSE, null];
        } else {
            return [TRUE, $execute_result];
        }    
    }

    // Check Duplicate Username Function (username [STRING])
    function Check_Duplicate($username){
        // Attempt to find account via username \\
        $query = "SELECT * FROM ODAccountsDB WHERE username = ?";
        list($execute_success, $execute_result) = Generate_Query($query, array('s', $username));

        // If no results return false, if results return true \\
        if(mysqli_num_rows($execute_result) == 0){
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // Account Auth Function (username [STRING], password [STRING])
    function Authenticate_User($username, $password){
        // Attempt to find account via username (plaintext) and password (plaintext->sha256_hash) \\
        $query = "SELECT * FROM ODAccountsDB WHERE username = ? AND password = ?";
        list($execute_success, $execute_result) = Generate_Query($query, array('s', $username), array('s', hash("sha256", $password)));
        
        // If success, check result \\
        if($execute_success == TRUE){
            // Make results actually exist \\
            if(mysqli_num_rows($execute_result) != 0){
                // Found account with valid credentials, return true and account data \\
                $data = $execute_result->fetch_assoc();
                return [TRUE, $data];
            } else {
                // No accounts returned, return false and null \\
                return [FALSE, null];
            }
        } else {
            // Execute failed, return false and null \\
            return [FALSE, null];
        }        
    }

    // Account Data Function (userid [INT])
    function Get_User_Data($id){
        // Attempt to find account via username (plaintext) and password (plaintext->sha256_hash) \\
        $query = "SELECT * FROM ODAccountsDB WHERE user_id = ?";
        list($execute_success, $execute_result) = Generate_Query($query, array('i', $id));
        
        // If success, check result \\
        if($execute_success == TRUE){
            // Make results actually exist \\
            if(mysqli_num_rows($execute_result) != 0){
                // Found account with valid credentials, return true and account data \\
                $data = $execute_result->fetch_assoc();
                return [TRUE, $data];
            } else {
                // No accounts returned, return false and null \\
                return [FALSE, null];
            }
        } else {
            // Execute failed, return false and null \\
            return [FALSE, null];
        }        
    }    

    // Session Expiry Check Function 
    function Check_Session_Expire(){
        // Check if session is set \\
        if(isset($_SESSION['expire'])){
          // Get current time and see if bigger or equal to session expire time (EPOCH) \\
          $current_time = time();
          if($current_time >= $_SESSION['expire']){
            session_unset();
            session_destroy();
          }
        } 
    }

    // Return Venues Function
    function Return_Venues(){
        // Make the query \\
        $query = "SELECT * FROM ODVenueDB";
        list($execute_success, $execute_result) = Generate_Query($query);

        // Check for query execution success - return false, null if not successful or true and data if successful \\
        if($execute_success == FALSE){
            return [FALSE, null];
        } else {
            return [TRUE, $execute_result];
        }
    }

    // Register Attendance Function (username [STRING], password [STRING])
    function Register_Attendance($username, $password){
        // Authenticate new user account \\
        list($valid, $data) = Authenticate_User($username, $password);
        if($valid){
            // Register attendance to database \\
            $query = "INSERT INTO ODAttendanceDB (user_id) VALUES (?)";
            list($execute_success, $execute_result) = Generate_Query($query, array('i', $data['user_id']));

            // Return success (TRUE/FALSE) \\
            return $execute_success;
        } else {
            // Invalid credentials, return false \\
            return FALSE;
        }
    }

    // Delete Account Function (username [STRING], password [STRING])
    function Delete_Account($username, $password){
        // Attempt to find account \\
        $query = "SELECT * FROM ODAccountsDB WHERE username = ? AND password = ?";
        list($execute_success, $execute_result) = Generate_Query($query, array('s', $username), array('s', hash("sha256", $password)));
        
        // If execution success, delete account \\
        if($execute_success == TRUE){
            if(mysqli_num_rows($execute_result) != 0){
                $data = $execute_result->fetch_assoc();
                $dquery = "DELETE FROM ODAccountsDB WHERE user_id = ?";
                list($dexecute_success, $dexecute_result) = Generate_Query($dquery, array('i', $data['user_id']));
                return [$dexecute_success, $dexecute_result];
            } else {
                // No accounts found to delete, return false and null \\
                return [FALSE, null];
            }
        } else {
            // Execution of query failed, return false and null \\
            return [FALSE, null];
        }           
    }

    /*                         
       PUBLIC FUNCTION SECTION
    */                         

    // Register Staff Account Function (username [STRING], password [STRING], secret_username [STRING], secret_password [STRING] (UNHASHED))
    function Register_Account_Staff($username, $password, $secret_username, $secret_password){

    // If user logged in, return failure response \\
    if(isset($_SESSION['user_id'])){
        Generate_ResponseJSON(FALSE, 'ERROR - You must be logged out to access this endpoint', null);
        die();
    } 

     // Ensure username is not a duplicate/not already taken \\
     if(Check_Duplicate($username)){
        Generate_ResponseJSON(FALSE, 'ERROR - Username already in use', array('username' => $username));
        die();
     }

     // Check for static secret username and static secret password (hashed in sha256 from plaintext) \\
     if($secret_username == 'secret_bpw@197!' and hash("sha256", $secret_password) == '0e956f3f588f1e97e8ae10abfef917a463601c1e1e267e297ded1194613c352c'){

      $query = "INSERT INTO ODAccountsDB (username, password, user_level) VALUES (?, ?, ?)";
      list($execute_success, $execute_result) = Generate_Query($query, array('s', $username), array('s', hash("sha256", $password)), array('i', 1));

      // Check if staff account registration succeeded \\
      if($execute_success == TRUE){
        // Give success response and kill php runtime \\
        Generate_ResponseJSON(TRUE, 'SUCCESS - Account has been registered.', array('username' => $username, 'password' => hash("sha256", $password)));
        die();
      } else {
        // Give failure response and kill php runtime \\
        Generate_ResponseJSON(FALSE, 'ERROR - Query Dropped', null); 
        die();
      }

     } else {
      // If secret username or secret password incorrect for creating staff accounts, give response and kill php runtime \\
      Generate_ResponseJSON(FALSE, 'ERROR - Secret username or Secret password are incorrect - unauthorized to access this endpoint.', null); 
      die();
     }

    }

    // Register Account Function (username [STRING], password [STRING])
    function Register_Account_User($username, $password, $register_code){

        // If user logged in, return failure response \\
        if(isset($_SESSION['user_id'])){
            Generate_ResponseJSON(FALSE, 'ERROR - You must be logged out to access this endpoint', null);
            die();
        } 

        // Get the access code \\
        $dynamic_code = '';
        list($success, $execute_result) = Return_Access_Code();
        if(!$success){
            Generate_ResponseJSON(FALSE, 'ERROR - There is no valid access code at this time', null);
            die();
        } else {
            $data = $execute_result->fetch_assoc();
            $dynamic_code = $data['code'];
        }

        // hard-coded registration code for attendance - I3OXJ5C8skU
        if(strtolower($register_code) != $dynamic_code){
            Generate_ResponseJSON(FALSE, 'ERROR - Your registration code is invalid', null);
           die();
        } 

        // Ensure username is not a duplicate/not already taken \\
        if(Check_Duplicate($username)){
            Generate_ResponseJSON(FALSE, 'ERROR - Username already in use', array('username' => $username));
            die();
        }

        // Create the account, hashing the password and storing username in plain text \\
        $query = "INSERT INTO ODAccountsDB (username, password, user_level) VALUES (?, ?, ?)";
        list($execute_success, $execute_result) = Generate_Query($query, array('s', $username), array('s', hash("sha256", $password)), array('i', 0));

        if($execute_success == TRUE){
        
           // Attempt to register attendance on register of account, if failed delete account \\
           $attendance_register_success = Register_Attendance($username, $password);
           if($attendance_register_success == FALSE){
            Delete_Account($username, $password);
            Generate_ResponseJSON(FALSE, 'ERROR - Failed to register your account as attended.', null);
            die();
           }

           // Attempt to automatically login user \\
           list($valid, $data) = Authenticate_User($username, $password);
           if($valid){
            $_SESSION['user_id'] = $data['user_id']; // user_id for session
            $_SESSION['start'] = time(); // time when session was created, for expiry.
            $_SESSION['expire'] = $_SESSION['start'] + (60 * 43800); // expires in 1 month            
           }

           // Registration success response & kill php runtime \\
           Generate_ResponseJSON(TRUE, 'SUCCESS - Account has been registered.', array('username' => $username, 'password' => hash("sha256", $password)));
           die();
        } else {
           // Registration failure response & kill php runtime \\
           Generate_ResponseJSON(FALSE, 'ERROR - Query Dropped', null); 
           die();
        }

    }

    // Login Account Function (method [$_GET/$_POST], username [STRING], password [STRING])
    function Login($username, $password){
        // Check if user is already logged in \\
        if(isset($_SESSION['user_id'])){
            Generate_ResponseJSON(FALSE, 'ERROR - You are already logged in', null);
            die();
        } else {
            // Attempt to login using username and password \\
            list($valid, $data) = Authenticate_User($username, $password);
            if($valid){
                // Valid credentials, set session, give response and kill php runtime \\
                Generate_ResponseJSON(TRUE, 'SUCCESS - You have been logged in', $data['user_id']);
                $_SESSION['user_id'] = $data['user_id']; // user_id for session
                $_SESSION['start'] = time(); // time when session was created, for expiry.
                $_SESSION['expire'] = $_SESSION['start'] + (60 * 43800); // expires in 1 month
                die();
            } else {
                // Invalid Credentials, give response and kill php runtime \\
                Generate_ResponseJSON(FALSE, 'ERROR - Invalid username or password', null);
                die();
            }
        }
    }

    // Logout Account Function 
    function Logout(){
        // Check for active session, if active give response and kill php runtime \\
        if(session_status() != PHP_SESSION_ACTIVE){
            Generate_ResponseJSON(FALSE, 'ERROR - You are not logged in', null);
            die();
        } else {
            // If active session, unset session, destroy session, remove session cookie, give response and kill php runtime \\
            session_unset();
            session_destroy();
            setcookie(session_name(), '', time() - 3600, '/');
            Generate_ResponseJSON(TRUE, 'SUCCESS - You have been logged out', null);
            die();
        }
    }

    // List courses function
    list($venue_success, $venue_data) = Return_Venues();
    function Search_Courses($search_filter){
        
        // If user not logged in, return failure response \\
        if(!isset($_SESSION['user_id'])){
            Generate_ResponseJSON(FALSE, 'ERROR - You must be logged in to access this endpoint.', null);
            die();
        } 

        // Search for courses matching search or similar to search \\
        if($search_filter != null){
            // Make the query \\
            $query = "SELECT * FROM ODCoursesDB WHERE course_name LIKE ? OR course_name LIKE ?";
            list($execute_success, $execute_result) = Generate_Query($query, array('s', $search_filter), array('s', '%' . $search_filter . '%'));

            // Check for query execution success, failure response if FALSE \\
            if($execute_success == FALSE){
                Generate_ResponseJSON(FALSE, 'ERROR - Query Dropped', null); 
                die();              
            } else {
                // Check for results, failure response if none else form json response \\
                if(mysqli_num_rows($execute_result) == 0){
                    // Failure response \\
                    Generate_ResponseJSON(FALSE, 'No courses were found', null); 
                    die();                        
                } else {
                    // Neatly put all courses and their data into a list \\
                    while ($row = $execute_result->fetch_assoc()) {
                        $courses[] = $row;
                    }

                    // Neatly put all venues and their data into a list if success \\
                    if($GLOBALS['venue_success']){
                        while($row = $GLOBALS['venue_data']->fetch_assoc()){
                            $venues[] = $row;
                        }
                    } else {
                        $venues = [];
                    }

                    // Create return Data \\
                    $return_data = array('courses' => $courses, 'venues' => $venues);

                    Generate_ResponseJSON(TRUE, 'SUCCESS - Courses have been returned', $return_data);
                }
            }
        } else {
            // Make the query \\
            $query = "SELECT * FROM ODCoursesDB";
            list($execute_success, $execute_result) = Generate_Query($query);

            // Check for query execution success, failure response if FALSE \\
            if($execute_success == FALSE){
                Generate_ResponseJSON(FALSE, 'ERROR - Query Dropped', null); 
                die();              
            } else {
                // Check for results, failure response if none else form json response \\
                if(mysqli_num_rows($execute_result) == 0){
                    // Failure response \\
                    Generate_ResponseJSON(FALSE, 'No courses were found', null); 
                    die();                        
                } else {
                    // Neatly put all courses and their data into a table \\
                    while ($row = $execute_result->fetch_assoc()) {
                        $courses[] = $row;
                    }

                    // Neatly put all venues and their data into a list if success \\
                    if($GLOBALS['venue_success']){
                        while($row = $GLOBALS['venue_data']->fetch_assoc()){
                            $venues[] = $row;
                        }
                    } else {
                        $venues = [];
                    }

                    // Create return Data \\
                    $return_data = array('courses' => $courses, 'venues' => $venues);

                    Generate_ResponseJSON(TRUE, 'SUCCESS - Courses have been returned', $return_data);
                }
            }
        }
    }

    function Refresh_Access_Code(){
        // If user not logged in, return failure response \\
        if(!isset($_SESSION['user_id'])){
            Generate_ResponseJSON(FALSE, 'ERROR - You must be logged in to access this endpoint', null);
            die();
        } 
        
        // Attempt to find data using user id \\
        list($valid, $data) = Get_User_Data($_SESSION['user_id']);

        // If Data Found, check against it \\
        if($valid){
            // Check user level, if success refresh access code, otherwise failure response \\
            if($data['user_level'] >= 1){
                // Generate new code \\
                $new_code = Generate_Code();

                // Make the query \\
                $query = "UPDATE ODCodesDB SET code = ? WHERE code_id = 1";
                list($execute_success, $execute_result) = Generate_Query($query, array('s', $new_code));

                // Check for results, failure response if not execute success \\
                if($execute_success == TRUE){
                    Generate_ResponseJSON(TRUE, 'SUCCESS - Access Registration Code has been regenerated', array('code' => $new_code));
                } else {
                    Generate_ResponseJSON(FALSE, 'ERROR - Query Dropped', null);
                }
                die();
            }  else {
                Generate_ResponseJSON(FALSE, 'ERROR - You are not authorized to access this endpoint', null);
                die();
            }
        } else {
            // No data found, return failure response \\
            Generate_ResponseJSON(FALSE, 'ERROR - You are not authorized to access this endpoint', null);
            die();
        }
    }
?>
