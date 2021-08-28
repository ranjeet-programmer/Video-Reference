<?php 

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    session_start();
   
    require_once('includes/connect.php');
    require_once('includes/smtp.php');

    $url = "http://localhost/project/";

    require 'PHPMailer-master/src/Exception.php';
    require 'PHPMailer-master/src/PHPMailer.php';
    require 'PHPMailer-master/src/SMTP.php';

    if(isset($_POST) & !empty($_POST))
    {
        if(empty($_POST['password']))
        { 
            $errors[] = 'Password field is Required';
        }
        else
        {
            if(empty($_POST['passwordr']))
            { 
                $errors[] = 'Repeat Password field is Required';
            }
            else
            {
                // compare both password, if they match. generate the password hash
                if($_POST['password'] == $_POST['passwordr']){
                    // create password hash
                    $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }else{
                    // error message
                    $errors[] = 'Both Passwords Should Match';
                }
            }
    }

    // CSRF Token Validation
    if(isset($_POST['csrf_token']))
    {
        if($_POST['csrf_token'] === $_SESSION['csrf_token']){
        }else{
            $errors[] = "Problem with CSRF Token Verification";
        }
    }
    else
    {
        $errors[] = "Problem with CSRF Token Validation";
    }

    // CSRF Token Time Validation
    $max_time = 60*60*24;

    if(isset($_SESSION['csrf_token_time']))
    {
        $token_time = $_SESSION['csrf_token_time'];
        if(($token_time + $max_time) >= time()){
        }else{
            $errors[] = "CSRF Token Expired";
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
        }
    }
    else
    {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
    }

    if(empty($errors))
    {
        // Update the password after submitting new password
        $sql = "SELECT * FROM password_reset WHERE reset_token=:reset_token AND uid=:uid";
        $result = $db->prepare($sql);
        $values = array(':reset_token'      => $_POST['key'],
                        ':uid'              => $_POST['id']
                        );
        $result->execute($values);
        $count = $result->rowCount();

        if($count == 1)
        {
            // update the password here
            $updsql = "UPDATE users SET password=:password, updated=NOW() WHERE id=:id";
            $updresult = $db->prepare($updsql);
            $values = array(':password' => $pass_hash,
                            ':id'       => $_POST['id']
                            );
            $updres = $updresult->execute($values);

            $usersql = "SELECT * FROM users WHERE id=?";
            $userresult = $db->prepare($usersql);
            $userresult->execute(array($_POST['id']));
            $user = $userresult->fetch(PDO::FETCH_ASSOC);

            if($updres)
            {
                // Inserting activity into DB table
                $actsql = "INSERT INTO user_activity (uid, activity) VALUES (:uid, :activity)";
                $actresult = $db->prepare($actsql);
                $values = array(':uid'          => $_POST['id'],
                                ':activity'     => 'Password Updated with Reset Password'
                                );
                $actresult->execute($values);

                // deleting the reset token from password_reset table
                $delsql = "DELETE FROM password_reset WHERE reset_token=?";
                $delresult = $db->prepare($delsql);
                $delres = $delresult->execute(array($_POST['key']));
                if($delres)
                {
                    // send email
                    
                    $mail = new PHPMailer(true);

                    try {
                        //Server settings
                        $mail->isSMTP();                                            // Set mailer to use SMTP
                        $mail->Host       = $smtphost;  // Specify main and backup SMTP servers
                        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                        $mail->Username   = $smtpuser;                     // SMTP username
                        $mail->Password   = $smtppass;                               // SMTP password
                        $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
                        $mail->Port       = 587;                                    // TCP port to connect to

                        //Recipients
                        $mail->setFrom($fromemail, $fromname);
                        // TODO : update recipient email with dynamic email
                        $mail->addAddress($user['email'], $user['username']);     // Add a recipient

                        // Content
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = 'Password Updated';
                        $mail->Body    = "Your Account Password Updated, Login to your account</b>";
                        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                        $mail->send();
                        $messages[] = 'Password Update Confirmation Email Sent';
                        // we can redirect the user to login page
                        header('location: login.php');
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }

            }
        }else{
            $errors[] = "There is some problem with Reset Token, Contact Site Admin!";
        }
    }
}   

// Create CSRF token
$token = md5(uniqid(rand(), TRUE));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();


    // fetch the user details from database and display those details in disabled input fields, username & email
    $sql = "SELECT * FROM password_reset WHERE reset_token=:reset_token AND uid=:uid";
    $result = $db->prepare($sql);
    $values = array(':reset_token'      => $_GET['key'],
                    ':uid'              => $_GET['id']
                    );
    $result->execute($values);
    $count = $result->rowCount();
    if($count == 1){
        // Select SQL query to fetch user details from users table using user id
        $usersql = "SELECT * FROM users WHERE id=? AND activate=1";
        $userresult = $db->prepare($usersql);
        $userresult->execute(array($_GET['id']));
        $usercount = $userresult->rowCount();
        $userres = $userresult->fetch(PDO::FETCH_ASSOC);
        if($usercount == 1){
            //$messages[] = "Do Nothing, display the details in form";
        }else{
            $errors[] = "Your Account is not Active, Please activate before resetting the password";
        }
    }else{
        $errors[] = "There is some problem with Reset Token, Contact Site Admin!";
    }

?>

<!DOCTYPE html>
<html>
<head>
	<title>Update Password</title>

	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">


  	<!-- Font Awesome CDN -->

	 <script src="https://kit.fontawesome.com/376a7cdf60.js" crossorigin="anonymous"></script>
	

</head>
<body>

		<div class="form-gap"></div>
			<div class="container">
				<div class="row">
					<div class="col-md-4 col-md-offset-4">
			            <div class="panel panel-default">
			              <div class="panel-body">

			              	<?php

                                if(!empty($messages))
                                {
                                    echo "<div class='alert alert-success'>";
                                    foreach ($messages as $message) {
                                        
                                        echo "<span class='glyphicon glyphicon-ok'></span>&nbsp;"
                                        .$message."<br>";
                                    }

                                    echo "</div>";
                                }

                            ?>

                            <?php 

                                if(!empty($errors))
                                {
                                    echo "<div class='alert alert-danger'>";
                                    foreach($errors as $error)
                                    {
                                        echo "<span class='glyphicon glyphicon-remove'></span>" .$error. "<br>";
                                    }
                                    echo "</div>";
                                }

                            ?>

			                <div class="text-center">
			                  <h3><i class="fa fa-lock fa-4x"></i></h3>
			                  <h2 class="text-center">Update Password?</h2>
			                  <p>You can reset your password here.</p>
			                  <div class="panel-body">
			    
			                    <form id="register-form" role="form" autocomplete="off" class="form" method="post">
			    				<input type="hidden" class="hide" name="csrf_token" id="token" value="<?php echo $token ; ?>">
                                <input type="hidden" class="hide" name="key" id="token" value="<?php echo $_GET['key'] ; ?>">
                                <input type="hidden" class="hide" name="id" id="token" value="<?php echo $_GET['id'] ; ?>">

			                      <div class="form-group">
			                        <div class="input-group">
			                          <span class="input-group-addon"><i class="glyphicon glyphicon-envelope color-blue"></i></span>
			                          <input id="username" name="username" placeholder=" username" disabled class="form-control"  type="text"value="<?php if(isset($userres['username'])){echo $userres['username'];} ?>" >
			                        </div>
			                      </div>

                                  <div class="form-group">
                                    <div class="input-group">
                                      <span class="input-group-addon"><i class="glyphicon glyphicon-envelope color-blue"></i></span>
                                      <input id="email" name="email" placeholder="email " disabled  class="form-control"  type="text"value="<?php if(isset($userres['email'])){echo $userres['email'];} ?>">
                                    </div>
                                  </div>
			                      
                                   <div class="form-group">
                                    <div class="input-group">
                                      <span class="input-group-addon"><i class="fa fa-lock " aria-hidden="true"></i></span>
                                      <input id="password" name="password" placeholder="password "   class="form-control"  type="password">
                                    </div>
                                  </div>

                                 <div class="form-group">
                                    <div class="input-group">
                                      <span class="input-group-addon"><i class="fa fa-lock " aria-hidden="true"></i></span>
                                      <input id="passwordr" name="passwordr" placeholder=" confirm password "   class="form-control"  type="password">
                                    </div>
                                  </div>

                                 <div class="form-group">
                                    <input name="recover-submit" class="btn btn-lg btn-primary btn-block" value="Change Password" type="submit">
                                  </div>
			                     
			                    </form>
			    
			                  </div>
			                </div>
			              </div>
			            </div>
			          </div>
				</div>
			</div>
		

		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
		<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>

</body>
</html>