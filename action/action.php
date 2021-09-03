<?php
header("Access-Control-Allow-Origin: *");
include_once('../db/Db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // $error = [];
    $error_count = 0;
    $email_error = $password_error = $error = $username_error = '';
    $_POST = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_POST['login']) && $_POST['login'] == 'login') {
        if (empty($_POST['email'])) {
            $email_error = 'Email is required';
            $error_count++;
        }else {
            $email = test_input($_POST["email"]);
            // check if e-mail address is well-formed
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $email_error = 'Invalid email format';
              $error_count++;
            }
        }

        if (empty($_POST['password'])) {
            $password_error = 'Password is required';
            $error_count++;
        }elseif (strlen($_POST['password']) < 6 ) {
            $password_error = 'Password must be at least 6 character';
            $error_count++;
        }else{
            $password = test_input($_POST["password"]);
        }

        if ($error_count === 0) {
            if (!empty($password) && !empty($email)) {
                $query = "SELECT * FROM users where email = :email";
                $statement = $conn->prepare($query);
                $statement->execute(array(
                    ':email' => $email
                ));

                $row_count = $statement->rowCount();
                
                if ($row_count > 0) {
                    $result = $statement->fetchAll();

                    foreach ($result as $row) {

                        /* echo password_hash($password, PASSWORD_DEFAULT);
                        echo json_encode($row['password']);
                        echo json_encode(password_verify($password, $row['password']));
                        die(); */
                        
                        if (password_verify($password, $row['password'])) {
                            $_SESSION['user_id'] = $row['id'];
                        }else{
                            $error = 'Email or Password incorrect';
                            $error_count++;
                        }

                    }
                }else{
                    $email_error = 'Email does not exit';
                    $error_count++;
                }
            }

        }


        if ($error_count > 0) {
            $output = array(
                'error'     => true,
                'email_error' => $email_error,
                'password_error' => $password_error,
                'errors' => $error
            );
        }else{
            $output = array(
                'success'  => true
            );
        }

        echo json_encode($output);
    }




    // For register
    if (isset($_POST['register']) && $_POST['register'] == 'register') {
        $email_exist = false;
        $user_exist = false;

        if (empty($_POST["username"])) {
            $nameErr = "Username is required";
        } else {
            $username = test_input($_POST["username"]);
            // check if name only contains letters and whitespace
            if (!preg_match("/^[a-zA-Z]*$/",$username)) {
                $username_error = "Only letters without space";
                $error_count++;
            }
        }

        if (empty($_POST['email'])) {
            $email_error = 'Email is required';
            $error_count++;
        } else {
            $email = test_input($_POST["email"]);
            // check if e-mail address is well-formed
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $email_error = 'Invalid email format';
              $error_count++;
            }
        }

        if (empty($_POST['password'])) {
            $password_error = 'Password is required';
            $error_count++;
        }elseif (strlen($_POST['password']) < 6 ) {
            $password_error = 'Password must be at least 6 character';
            $error_count++;
        }else{
            $password = password_hash(test_input($_POST["password"]), PASSWORD_DEFAULT);
        }


        if ($error_count === 0) {
            $data = array(
                ':username'    => $username,
                ':email'  => $email,
                ':password'    => $password
            );

            if (!empty($password) && !empty($email) && !empty($username)) {
                $query = "SELECT * FROM users where username = :username";
                $statement = $conn->prepare($query);
                $statement->execute(array(
                    ':username' => $username
                ));

                $row_count = $statement->rowCount();
                if ($row_count > 0) {
                    $username_error = 'Username already exist';
                    $error_count++;
                    $user_exist = true;
                }else{
                    $query = "SELECT * FROM users where email = :email";
                    $statement = $conn->prepare($query);
                    $statement->execute(array(
                        ':email' => $email
                    ));

                    $row_count = $statement->rowCount();
                    if ($row_count > 0) {
                        $email_error = 'Email already exit';
                        $error_count++;
                        $email_exist = true;
                    }
                }


                // if email doesnt exist and username then insert into the database
                if ($email_exist === false && $user_exist === false) {
                    $query = "INSERT INTO users (username, email, password) VALUES(:username, :email, :password)";
                    $statement = $conn->prepare($query);
                    $statement->execute($data);
                }else{
                    $error = "Error occur while registeration is on!";
                    $error_count++;
                }
            }

        }

        if ($error_count > 0) {
            $output = array(
                'error'     => true,
                'username_error' => $username_error,
                'email_error' => $email_error,
                'password_error' => $password_error,
                'errors' => $error
            );
        }else{
            $output = array(
                'success'  => true,
                'success_message' => 'Registeration Successfully'
            );
        }

        echo json_encode($output);
    }

    if ($_POST['action'] == "check-username" && isset($_POST['username'], $_POST['action']) && $_POST['username'] !== '') {

        if (empty($_POST["username"])) {
            $nameErr = "Username is required";
        } else {
            $username = test_input($_POST["username"]);
            // check if name only contains letters and whitespace
            if (!preg_match("/^[a-zA-Z]*$/",$username)) {
                $username_error = "Only letters without space";
                $error_count++;
            }
        }

        if ($error_count === 0) {
            $query = "SELECT * FROM users where username = :username";
            $statement = $conn->prepare($query);
            $statement->execute(array(
                ':username' => $username
            ));

            $row_count = $statement->rowCount();
            if ($row_count > 0) {
                $username_error = 'Username already exist';
                $error_count++;
                $user_exist = true;
            }else{
                $user_exist = false;
            }
        }

        if ($error_count > 0 && $user_exist == true) {
            $output = array(
                'error'     => true,
                'username_error' => $username_error
            );
        }else{
            $output = array(
                'success'  => true,
                'success_message' => 'Username is available'
            );
        }

        echo json_encode($output);
        
    }




}


function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }