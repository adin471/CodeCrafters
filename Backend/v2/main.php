<?php

    /*                        \*
       LOCAL FUNCTION SECTION
    */                        \*

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

    /*                         \*
       PUBLIC FUNCTION SECTION
    */                         \*

    // Register Account Function (username [STRING], password [STRING], secret_username [STRING], secret_password [STRING] (UNHASHED))
    function Register_Account($username, $password, $secret_username, $secret_password){
     if($secret_username == 'secret_bpw@197!' and hash("sha256", $secret_password) == 'dc6c6ea2e0a4f5aa79780fb96172665b902fa1f1ef08ccf183289f70f3e590fa'){
      $query = "INSERT INTO ODAccountsDB (username, password) VALUES (?, ?)";
      list($execute_success, $execute_result) = Generate_Query($query, array('s', $username), array('s', hash("sha256", $password)));
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
?>
