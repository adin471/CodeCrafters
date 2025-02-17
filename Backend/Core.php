<?php

    // You will never know what this is for so dont bother looking for the server the mysql is hosted on..

    // ULTRA PRIVATE

    function Connect_To_Database(){
        $mysqli = mysqli_connect("localhost", "2378946", "kr8n8s", "db2378946");
        if ($mysqli -> connect_errno) {
            echo "Failed to connecto to MySQL: " . $mysqli -> connect_error;
            exit();
        }
        return $mysqli;
    }



    // PRIVATE 


    function Array_To_JSON($Array){
        echo(json_encode($Array));
    }

    // Example Usage: Generate_Query('SELECT * FROM CodesDB WHERE Code = ?', array('s', 'dkfjud'))
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

    function Generate_ResponseJSON($Success, $Message, $Data){
        echo(json_encode([
            "Success" => $Success,
            "Message" => $Message,
            "Data" => $Data
        ]));
    }

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

    function Add_Code_For_Venue($Venue){       
        // Connect to database
        $mysqli = Connect_To_Database();

        // Variables 
        $Code = '';
        $valid = FALSE;
        $attempt = 0;

        // Generate code(s) until one is unique, so valid.
        while($valid == FALSE){
            $Code = Generate_Code();

            // Make query, prevent sql injection.
            $query = "SELECT * FROM CodesDB WHERE Code = ?";
            $query_data = Generate_Query($query, array('s', $Code));

            if($attempt >= 5){
                Array_To_JSON(array(
                    "Success" => FALSE,
                    "Message" => "Failed to generate code - Query Dropped",
                    "Code" => NULL
                ));
                die();                
            }

            if($query_data[0] == TRUE){
                $query_result = $query_data[1];
                $query_rows = mysqli_num_rows($query_result);

                if($query_rows == 0){
                    $valid = TRUE;
                }
            } 

            $attempt=$attempt+1;
        }

        // Make the query, preventing sql injection.
        $query = "INSERT INTO CodesDB (Code, Venue) VALUES (?, ?)";
        $query_data = Generate_Query($query, array('s', $Code), array('s', $Venue));

        // Check query success, return TRUE (1) or FALSE (0)
        if($query_data[0] == TRUE){
            $mysqli->close();
            Array_To_JSON(array(
                "Success" => TRUE,
                "Message" => "Succesfully added a code for $Venue",
                "Code" => strval($Code)
            ));
        } else {
            Array_To_JSON(array(
                "Success" => FALSE,
                "Message" => "Failed to add code for $Venue - Query Dropped",
                "Code" => NULL
            ));
        }
        die();
    }



    // PUBLIC

    function Check_Active_Code($Code){

        // Connect to database
        $mysqli = Connect_To_Database();

        // Make the query, preventing sql injection.
        $query = "SELECT * FROM CodesDB WHERE Code = ?";
        $query_data = Generate_Query($query, array('s', $Code));

        if($query_data[0] == TRUE){
            $query_result = $query_data[1];
        } else {
            return FALSE;
        }

        $query_rows = mysqli_num_rows($query_result);

        // Check against row count, if row count = 0 then return false - does not exist
        if($query_rows == 0){
            Array_To_JSON(array(
                "Success" => FALSE,
                "Message" => "This code does not exist - Inactive/Deleted",
                "Code" => strval($Code)
            ));
            die();
        } elseif($code = $query_result->fetch_assoc()){
            return $code['Venue'];
        }
    }

    function Register_Attendance($Firstname, $Surname, $Code, $HWID){
        // Connect to database
        $mysqli = Connect_To_Database();

        // Check for active code
        $Venue = Check_Active_Code($Code);

        // If venue is not invalid, register attendance.
        if($Venue != FALSE){

            // Make the query, preventing sql injection.
            $query = "INSERT INTO AttendanceDB (Venue, HWID, Firstname, Surname) VALUES (?, ?, ?, ?)";

            $query_data = Generate_Query($query, array('s', $Venue), array('s', $HWID), array('s', $Firstname), array('s', $Surname));

            if($query_data[0] == TRUE){
                Array_To_JSON(array(
                    "Success" => TRUE,
                    "Message" => "Successfully Registered Attendance",
                    "Venue" => strval($Venue)
                ));
            } else {
                Array_To_JSON(array(
                    "Success" => FALSE,
                    "Message" => "Failed to Register Attendance - Query Dropped",
                    "Venue" => NULL
                ));
            }
            die();
        } else {
            Array_To_JSON(array(
                "Success" => FALSE,
                "Message" => "Failed to Register Attendance - Venue not found",
                "Venue" => NULL
            ));
            die();
        }
    }
?>
