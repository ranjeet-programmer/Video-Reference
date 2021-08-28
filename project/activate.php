<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    session_start();

    require_once('includes/connect.php');
    require_once('includes/smtp.php');

    require 'PHPMailer-master/src/Exception.php';
    require 'PHPMailer-master/src/PHPMailer.php';
    require 'PHPMailer-master/src/SMTP.php';


       
       //Check Activation token in user_active table

        $sql = "SELECT * FROM user_active WHERE active_token = :active_token AND uid = :uid";
        $result = $db->prepare($sql);
        $values = array(':active_token' => $_GET['key'],
                        ':uid'          => $_GET['id'],
                        ) ;

        $result->execute($values);

        $count = $result->rowCount();

        if($count == 1)
        {

                $messages[] = "Account Exsits";
            // if the activation key exists , make the user an active  and remove the key from user_active table

            $updsql = "UPDATE users SET activate = :activate , updated=NOW() where id = :id";
            $updresult = $db->prepare($updsql);
            $values = array(':activate' => 1,
                            ':id'          => $_GET['id'],
                            ) ;

            $updresult->execute($values);

            if($updresult)
            {
                $messages[] = "Account Activated Successfully";

                //delete activation key record from user_active table

                $delsql = "DELETE * FROM user_active WHERE active_token = ?";
                $delresult = $db->prepare($delsql);
                $delresult->execute(array($_GET['key']));
                 $messages[] = "Prepairing your account for first time login";
            }

            // Adding Activity in users_activity table

            $actsql = "INSERT INTO user_activity (uid,activity) VALUES (:uid,:activity)";
            $actresult = $db->prepare($actsql);

            $values = array(
                            ':uid'          => $_GET['id'],
                            ':activity'     =>  "User Account Activated"
                            ) ;

            $actresult->execute($values);

            $messages[] = "Adding User Registeration to log Entry";
            $usersql = "SELECT * FROM users WHERE id=?";
            $userresult = $db->prepare($usersql);
            $userresult->execute(array($_GET['id']));
            $user = $userresult->fetch(PDO::FETCH_ASSOC);
            //send the  confirmation email to the user

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
                        $mail->Subject = 'Account Activated';
                        $mail->Body    = "Your Account Activated Please Login";
                        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                        $mail->send();
                        $messages[] = 'Activation Email Sent, Follow the Instructions';
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }

         }

        else
        {
            $errors[] = "Failed to Activate Your Account ,Please Contact Admin";
        }

        
        
       

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Register</title>

    <!-- Bootstrap CDN -->

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">

    <!-- custom css -->

    <link rel="stylesheet" type="text/css" href="css/register.css">

    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/376a7cdf60.js" crossorigin="anonymous"></script>



  </head>
  <body>

      <div class="container" >
        <div class="row justify-content-center">
                <div class="col-md-6">

                    <div class="card">
                        <div class="card-header text-center">Register</div>
                        <div class="card-body">

                            
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
                            
                        </div>
                    </div>
                </div>
            </div>
          </div>
  </body>
  </html>