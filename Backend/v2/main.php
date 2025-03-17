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

        $bind = $prepare->bind_param($bind_string, ...$bind_args);

        $execute = $prepare->execute();
        $result = $prepare->get_result();

        $mysqli->close();
        return [$execute, $result];
    }

    // JSON / API Response Generation Function (Success [TRUE/FALSE], Message [STRING], Data [...])
    function Generate_ResponseJSON($Success, $Message, $Data){
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

    // Check Duplicate Username Function  (username [STRING])
    function Check_Duplicate($username){
        $query = "SELECT * FROM ODAccountsDB WHERE username = ?";
        list($execute_success, $execute_result) = Generate_Query($query, array('s', $username));

        if(mysqli_num_rows($execute_result) == 0){
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // Account Auth Function (username [STRING], password [STRING])
    function Authenticate_User($username, $password){
        $query = "SELECT * FROM ODAccountsDB WHERE username = ? AND password = ?";
        list($execute_success, $execute_result) = Generate_Query($query, array('s', $username), array('s', hash("sha256", $password)));
        
        if($execute_success == TRUE){
            if(mysqli_num_rows($execute_result) != 0){
                $data = $execute_result->fetch_assoc();
                return [TRUE, $data];
            } else {
                return [FALSE, null];
            }
        } else {
            return [FALSE, null];
        }        
    }

    // Session Expiry Check Function 
    function Check_Session_Expire(){
        if(isset($_SESSION['expire'])){
          $current_time = time();
          if($current_time >= $_SESSION['expire']){
            session_unset();
            session_destroy();
          }
        } 
    }

    // Register Attendance Function (username [STRING], password [STRING])
    function Register_Attendance($username, $password){
        list($valid, $data) = Authenticate_User($username, $password);
        if($valid){
            $query = "INSERT INTO ODAttendanceDB (user_id) VALUES (?)";
            list($execute_success, $execute_result) = Generate_Query($query, array('i', $data['user_id']));
            return $execute_success;
        } else {
            return FALSE;
        }
    }

    // Delete Account Function (username [STRING], password [STRING])
    function Delete_Account($username, $password){
        $query = "SELECT * FROM ODAccountsDB WHERE username = ? AND password = ?";
        list($execute_success, $execute_result) = Generate_Query($query, array('s', $username), array('s', hash("sha256", $password)));
        
        if($execute_success == TRUE){
            if(mysqli_num_rows($execute_result) != 0){
                $data = $execute_result->fetch_assoc();
                $dquery = "DELETE FROM ODAccountsDB WHERE user_id = ?";
                list($dexecute_success, $dexecute_result) = Generate_Query($dquery, array('i', $data['user_id']));
                return [$dexecute_success, $dexecute_result];
            } else {
                return [FALSE, null];
            }
        } else {
            return [FALSE, null];
        }           
    }

    /*                         
       PUBLIC FUNCTION SECTION
    */                         

    // Register Staff Account Function (username [STRING], password [STRING], secret_username [STRING], secret_password [STRING] (UNHASHED))
    function Register_Account_Staff($username, $password, $secret_username, $secret_password){
        
     if(Check_Duplicate($username)){
        Generate_ResponseJSON(FALSE, 'ERROR - Username already in use', array('username' => $username));
        die();
     }

     if($secret_username == 'secret_bpw@197!' and hash("sha256", $secret_password) == '0e956f3f588f1e97e8ae10abfef917a463601c1e1e267e297ded1194613c352c'){

      $query = "INSERT INTO ODAccountsDB (username, password, user_level) VALUES (?, ?, ?)";
      list($execute_success, $execute_result) = Generate_Query($query, array('s', $username), array('s', hash("sha256", $password)), array('i', 1));

      if($execute_success == TRUE){
        Generate_ResponseJSON(TRUE, 'SUCCESS - Account has been registered.', array('username' => $username, 'password' => hash("sha256", $password)));
        die();
      } else {
        Generate_ResponseJSON(FALSE, 'ERROR - Query Dropped', null); 
        die();
      }

     } else {
      Generate_ResponseJSON(FALSE, 'ERROR - Secret username or Secret password are incorrect - unauthorized to access this endpoint.', null); 
      die();
     }

    }

    // Register Account Function (username [STRING], password [STRING])
    function Register_Account_User($username, $password, $register_code){

        // hard-coded registration code for attendance - I3OXJ5C8skU
        if($register_code != 'I3OXJ5C8skU'){
            Generate_ResponseJSON(FALSE, 'ERROR - Your registration code is invalid', null);
           die();
        } 

        if(Check_Duplicate($username)){
            Generate_ResponseJSON(FALSE, 'ERROR - Username already in use', array('username' => $username));
            die();
        }

        $query = "INSERT INTO ODAccountsDB (username, password, user_level) VALUES (?, ?, ?)";
        list($execute_success, $execute_result) = Generate_Query($query, array('s', $username), array('s', hash("sha256", $password)), array('i', 0));

        if($execute_success == TRUE){
        
           $attendance_register_success = Register_Attendance($username, $password);
           if($attendance_register_success == FALSE){
            Delete_Account($username, $password);
            Generate_ResponseJSON(FALSE, 'ERROR - Failed to register your account as attended.', null);
            die();
           } 

           Generate_ResponseJSON(TRUE, 'SUCCESS - Account has been registered.', array('username' => $username, 'password' => hash("sha256", $password)));
           die();
        } else {
           Generate_ResponseJSON(FALSE, 'ERROR - Query Dropped', null); 
           die();
        }

    }

    // Login Account Function (method [$_GET/$_POST], username [STRING], password [STRING])
    function Login($username, $password){
        if(isset($_SESSION['user_id'])){
            Generate_ResponseJSON(FALSE, 'ERROR - You are already logged in', null);
            die();
        } else {
            list($valid, $data) = Authenticate_User($username, $password);
            if($valid){
                Generate_ResponseJSON(TRUE, 'SUCCESS - You have been logged in', $data['user_id']);
                $_SESSION['user_id'] = $data['user_id']; // user_id for session
                $_SESSION['start'] = time(); // time when session was created, for expiry.
                $_SESSION['expire'] = $_SESSION['start'] + (60 * 43800); // expires in 1 month
                die();
            } else {
                Generate_ResponseJSON(FALSE, 'ERROR - Invalid username or password', null);
                die();
            }
        }
    }

    // Logout Account Function 
    function Logout(){
        if(session_status() != PHP_SESSION_ACTIVE){
            Generate_ResponseJSON(FALSE, 'ERROR - You are not logged in', null);
            die();
        } else {
            session_unset();
            session_destroy();
            setcookie(session_name(), '', time() - 3600, '/');
            Generate_ResponseJSON(TRUE, 'SUCCESS - You have been logged out', null);
            die();
        }
    }

?>
