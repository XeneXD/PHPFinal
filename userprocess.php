<?php
    $username = '';
    $password = '';
    $verify_password = '';
    $message = '';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $host ='localhost';
    $dbname = 'usjr';
    $db_user = 'root';
    $password  = 'root';
    

    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $verify_password = isset($_POST['verify_password']) ? $_POST['verify_password'] : '';

    
    try {
        if($password !== $verify_password){
            $message ="Passwords do not match";
            $password ='';
            $verify_password = '';

        }elseif (!empty($username) && !empty($password)){
        
        $root = '';    
        $db = new pdo("mysql:host=localhost;dbname=usjr", "root", "root");
        $db -> setAttribute(pdo::ATTR_ERRMODE, pdo::ERRMODE_EXCEPTION);
        
        $hashedPassword = password_hash($password,PASSWORD_BCRYPT);

        $sql = "INSERT INTO appusers values(name,password,null,?,?)";
        $insertPreparedStatement = $db -> prepare($sql);
       
        
        $insertPreparedStatement ->bindParam(1,$username,PDO::PARAM_STR);
        $insertPreparedStatement ->bindParam(2,$hashedPassword,PDO::PARAM_STR);

        $result = $insertPreparedStatement -> execute();

        if($result != null){
            echo "New user is added to the table.";
        } else {
            echo "User was not added to the table.";
        }
    }
    }catch (PDOException $e){
    echo "Database error: " .$e->getMessage(); 
    }catch(PDOException $e){
     echo "Error: " . $e->getMessage();
    }
    }
?>