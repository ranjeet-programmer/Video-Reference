<?php 

	use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

	session_start();

	require_once('includes/connect.php');

	require_once('includes/smtp.php');

    require 'PHPMailer-master/src/Exception.php';
    require 'PHPMailer-master/src/PHPMailer.php';
    require 'PHPMailer-master/src/SMTP.php';

    $url = "http://localhost/project/";

	if(isset($_POST) & !empty($_POST))
	{
		if(empty($_POST['email']))
        {
            $errors[] = "Email / Username field is required";
        }

        //CSRF Token Validation

        if(isset($_POST['csrf_token']))
        {
            if($_POST['csrf_token'] === $_SESSION['csrf_token'])
            {

            }
            else
            {
                $errors[] = "Problem with CSRF Token Verification";
            }
        }

        else
        {
            $errors[] = "Problem with CSRF Token Validation";
        }

        //csrf token time validation

        $max_time = 60*60*24;

        if(isset($_SESSION['csrf_token_time']))
        {
            $token_time = $_SESSION['csrf_token_time'];

            if(($token_time + $max_time) >= time())
            {

            }
            else
            {
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
        	$sql = "SELECT * FROM users WHERE ";

            if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            {
              $sql .= "email=?";
            }
            else
            {
              $sql .= "username=?";
            }

            $sql .= "AND activate = 1";
             $result = $db->prepare($sql);
             $result->execute(array($_POST['email']));
             $count = $result->rowCount();
             $res = $result->fetch(PDO::FETCH_ASSOC);
             $userid = $res['id'];

            if($count == 1)
            {
            	$messages[] = " Username / Email exists  , create reset token
            	and send email";

            	 //Generating and Inserting Reset Token into DB Table - user_active table

                     $reset_token = md5($res['username']).time();

                     $resetsql = "INSERT INTO password_reset(uid,reset_token) VALUES (:uid, :reset_token)";
                     $resetresult = $db->prepare($resetsql);
                     $values = array(
                                                   ':uid' => $userid,
                                                   ':reset_token' =>$reset_token,
                                                );
					  $resetresult->execute($values);

					  // Inserting Activity  into DB Table -

					  $actsql = "INSERT INTO user_activity(uid,activity) VALUES (:uid, :activity)";
                                $actresult = $db->prepare($actsql);
                                $values = array(
                                                   ':uid' => $userid,
                                                   ':activity' =>'Password Reset Initiated'
                                                );

                                $actres = $actresult->execute($values);
                                $messages[] = "Adding User Registeration to Activity Log";


					  // Send Email to User

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
	                        $mail->addAddress($res['email'], $res['username']);     // Add a recipient

	                        // Content
	                        $mail->isHTML(true);                                  // Set email format to HTML
	                        $mail->Subject = 'Reset Your Password';
	                        $mail->Body    = "{$url}reset-password.php?key={$reset_token}&id={$userid}</b>";
	                        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

	                        $mail->send();
	                        $messages[] = 'Password reset Email Sent, Follow the Instructions';
	                    } catch (Exception $e) {
	                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	                    }



            }
            else
            {
            	$errors[] = 'Your Account is not activated';
            }


        }
	}

	//create CSRF Token

    $token = md5(uniqid(rand(),TRUE));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();


 ?>

<!DOCTYPE html>
<html>
<head>
	<title>Reset Password</title>

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
			                  <h2 class="text-center">Forgot Password?</h2>
			                  <p>You can reset your password here.</p>
			                  <div class="panel-body">
			    
			                    <form id="register-form" role="form" autocomplete="off" class="form" method="post">
			    				<input type="hidden" class="hide" name="csrf_token" id="token" value="<?php echo $token ; ?>">

			                      <div class="form-group">
			                        <div class="input-group">
			                          <span class="input-group-addon"><i class="glyphicon glyphicon-envelope color-blue"></i></span>
			                          <input id="email" name="email" placeholder="email address or username" class="form-control"  type="text"value="<?php if(isset($_POST['email'])){echo $_POST['email'];} ?>">
			                        </div>
			                      </div>
			                      <div class="form-group">
			                        <input name="recover-submit" class="btn btn-lg btn-primary btn-block" value="Reset Password" type="submit">
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