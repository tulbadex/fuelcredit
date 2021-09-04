<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header("Access-Control-Allow-Origin: *");
include_once('../db/Db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // $error = [];
    $error_count = 0;
    $fname_error = $lname_error = $email_error = $password_error = $error = $phone_error = $username_error = '';
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
        die();
    }




    // For register
    if (isset($_POST['register']) && $_POST['register'] == 'register') {
        sleep(2);
        $email_exist = false;
        $user_exist = false;

        if (empty($_POST["fname"])) {
            $fname_error = "First Name is required";
            $error_count++;
        } else {
            $fname = test_input($_POST["fname"]);
            // check if name only contains letters and whitespace
            if (!preg_match("/^[a-zA-Z]{3,}$/",$fname)) {
                $fname_error = "First Name must be letter only and 3 charcter without space";
                $error_count++;
            }
        }

        if (empty($_POST["lname"])) {
            $lname_error = "Last Name is required";
            $error_count++;
        } else {
            $lname = test_input($_POST["lname"]);
            // check if name only contains letters and whitespace
            if (!preg_match("/^[a-zA-Z]{3,}$/",$lname)) {
                $lname_error = "Last Name must be letter only and 3 charcter without space";
                $error_count++;
            }
        }

        if (empty($_POST["username"])) {
            $username_error = "Username is required";
            $error_count++;
        } else {
            $username = test_input($_POST["username"]);
            // check if name only contains letters and whitespace
            if (!preg_match("/^[a-zA-Z0-9]{3,}$/",$username) && filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $username_error = "Username must be at least 3 character & can't be an email";
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

        if (empty($_POST["phone"])) {
            $phone_error = "Phone Number is required";
            $error_count++;
        } else {
            $phone = test_input($_POST["phone"]);
            // check if name only contains letters and whitespace
            if (!preg_match("/^[+](2347|2348|2349)[0-9]{9}$/",$phone)) {
                $phone_error = "Invalid phone number start with (+234)";
                $error_count++;
            }
        }


        if ($error_count === 0) {
            $data = array(
                ':fname'    => $fname,
                ':lname'    => $lname,
                ':username'    => $username,
                ':email'  => $email,
                ':password'    => $password,
                ':phone'    => $phone
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
                    $query = "INSERT INTO users (fname, lname, username, email, password, phone, created_at) VALUES(:fname, :lname, :username, :email, :password, :phone, NOW())";
                    $statement = $conn->prepare($query);
                    $statement->execute($data);
                    $email_subject = "Tanks for registering";
                    $email_content = "This is to notify you that you just signed up on our website";
                    $usere = $email;
                    $toname = $fname.' '.$lname;
                    mailClient($email_subject, $email_content, $usere, $toname);
                }else{
                    $error = "Error occur while registering!";
                    $error_count++;
                }
            }

        }

        if ($error_count > 0) {
            $output = array(
                'error'     => true,
                'fname_error' => $fname_error,
                'lname_error' => $lname_error,
                'username_error' => $username_error,
                'email_error' => $email_error,
                'password_error' => $password_error,
                'phone_error' => $phone_error,
                'errors' => $error
            );
        }else{
            $output = array(
                'success'  => true,
                'success_message' => 'Registeration Successfully'
            );
        }

        echo json_encode($output);
        die();
    }

    if (isset($_POST['action'], $_POST['username']) && $_POST['action'] == "check-username" && $_POST !== '') {
        $user_exist = false;

        if (empty($_POST["username"])) {
            $username_error = "Username is required";
            $error_count++;
        } else {
            $username = test_input($_POST["username"]);
            // check if name only contains letters and whitespace
            if (!preg_match("/^[a-z0-9]{3,}[A-Z]$/",$username) && filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $username_error = "Username must be at least 3 character & can't be an email";
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
        die();
        
    }




}


function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function mailClient($email_subject, $email_content, $usere, $toname, $sendersEmail=NULL){
	require_once __DIR__.'/../PHPMailer/src/Exception.php';
	require_once __DIR__.'/../PHPMailer/src/PHPMailer.php';
	require_once __DIR__.'/../PHPMailer/src/SMTP.php';
	/* $sendersEmail ??= "info@nairaload.com";
	echo $sendersEmail;
	die(); */

	//Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);

	try {
		//Server settings SMTP::DEBUG_SERVER
		// $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
		$mail->isSMTP();                                            //Send using SMTP
		$mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
		$mail->Username   = 'tulbadex@gmail.com';                     //SMTP username
		$mail->Password   = 'babatunde12345';                               //SMTP password
		// $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
		$mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
		$mail->SMTPSecure = 'tls';

		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		//Recipients
		$senderEmail = $sendersEmail ?? "tulbadex@gmail.com";
		
		$mail->setFrom($senderEmail, 'Ibrahim Registration Site');
		$mail->addAddress($usere, $toname);     //Add a recipient


		//Content
		// $mail->isHTML(true);                                  //Set email format to HTML
		$mail->Subject = $email_subject;
		$mail->Body    = $email_content;

		$mail->send();
		return 'success';
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}