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
        

        //PHP form validation

        if(empty($_POST['uname']))
        {
            $errors[] = "Username field is required";
        }

         if(empty($_POST['email']))
        {
            $errors[] = "Email field is required";
        }

         if(empty($_POST['mobile']))
        {
            $errors[] = "Password field is required";
        }

          if(empty($_POST['password']))
        {
            $errors[] = "Password field is required";
        }
        else
        {
              if(empty($_POST['passwordr']))
            {
                $errors[] = " Confirm Password field is required";

            }
            else
            {
                    //compairing both the passwords , if they are match display the password hash

                    if($_POST['password'] == $_POST['passwordr'])
                    {
                        //create password hash

                        $pass_hash = password_hash($_POST['password'],PASSWORD_DEFAULT);
                    }
                    else
                    {
                        //error message

                        $errors[] = " Both Password Should Match";
                    }
            }
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

        //password will be password hash

        //Insert Data into users table

        if(empty($errors))
         {

                    $sql = "INSERT INTO users (username,email,password) VALUES (:username,:email,:password)";

                    $result = $db->prepare($sql);

                    $values = array(':username' => $_POST['uname'],
                                ':email' => $_POST['email'],
                                ':password'=> $pass_hash,
                            );

                    $res = $result->execute($values);

                    if($res)
                     {
                            $messages[] = "User Registered";

                              //get the id from the last insert query  and insert a new record into user_info table  mobile_number column

                            $userid = $db->lastInsertID();
                            $uisql = "INSERT INTO user_info(uid,mobile) VALUES (:uid, :mobile)";
                            $uiresult = $db->prepare($uisql);
                            $values = array(
                                               ':uid' => $userid,
                                               ':mobile' => $_POST['mobile']
                                            );

                            $uires = $uiresult->execute($values);

                            if($uires)
                            {
                                $messages[] = "Added Users Meta Information";

                                //Insert Activity into user_activity table

                                $actsql = "INSERT INTO user_activity(uid,activity) VALUES (:uid, :activity)";
                                $actresult = $db->prepare($actsql);
                                $values = array(
                                                   ':uid' => $userid,
                                                   ':activity' =>'User Registered'
                                                );

                                $actres = $actresult->execute($values);
                                $messages[] = "Adding User Registeration to Activity Log";

                                //Generating and Inserting Activation Token into DB Table - user_active table

                                $active_token = md5($_POST['uname']).time();

                                $activesql = "INSERT INTO user_active(uid,active_token) VALUES (:uid, :active_token)";
                                $activeresult = $db->prepare($activesql);
                                $values = array(
                                                   ':uid' => $userid,
                                                   ':active_token' =>$active_token,
                                                );

                                 $activeresult->execute($values);

                                //send email to user with PHPMailer



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
                                $mail->addAddress($_POST['email'], $_POST['uname']);     // Add a recipient

                                // Content
                                $mail->isHTML(true);                                  // Set email format to HTML
                                $mail->Subject = 'Verify Your Email';
                                $mail->Body    = "{$url}activate.php?key={$active_token}&id={$userid}</b>";
                                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                                $mail->send();
                                $messages[] = 'Activation Email Sent, Follow the Instructions';
                            } catch (Exception $e) {
                                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                            }

                            }
                    }  
            } 
            // else
            // {
            //     $errors[] = "problem with captcha";
            // }
        }

    

    //create CSRF Token

    $token = md5(uniqid(rand(),TRUE));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();

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
    <script src="https://www.google.com/recaptcha/api.js" ></script>


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
                            <form role="form" method="post">

                                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>"> 

                                <div class="form-group">
                                    <label for="username" class="cols-sm-2 control-label">Username</label>
                                    <div class="cols-sm-10">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-users fa" aria-hidden="true"></i></span>&nbsp;&nbsp;
                                            <input type="text" class="form-control" name="uname" id="uname" placeholder="Enter your Username" autocomplete="off" autofocus 
                                            value="<?php if(isset($_POST['uname'])){echo $_POST['uname'];} ?>" />
                                            
                                        </div>
                                        <span id="unameresults"></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="cols-sm-2 control-label">Email</label>
                                    <div class="cols-sm-10">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-envelope fa" aria-hidden="true"></i></span>&nbsp;&nbsp;
                                            <input type="text" class="form-control" name="email" id="email" placeholder="Enter your Email" autocomplete="off"
                                            value="<?php if(isset($_POST['email'])){echo $_POST['email'];} ?>"/>
                                        </div>
                                        <span id="emailresults"></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="username" class="cols-sm-2 control-label">Mobile No</label>
                                    <div class="cols-sm-10">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-users fa" aria-hidden="true"></i></span>&nbsp;&nbsp;
                                            <input type="text" class="form-control" name="mobile" id="username" placeholder="Enter your Mobile Number" autocomplete="off"
                                            value="<?php if(isset($_POST['mobile'])){echo $_POST['mobile'];} ?>" />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password" class="cols-sm-2 control-label">Password</label>
                                    <div class="cols-sm-10">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-lock " aria-hidden="true"></i></span>&nbsp;&nbsp;
                                            <input type="password" class="form-control" name="password" id="password" placeholder="Enter your Password" autocomplete="off"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="confirm" class="cols-sm-2 control-label">Confirm  Password</label>
                                    <div class="cols-sm-10">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-lock " aria-hidden="true"></i></span>&nbsp;&nbsp;
                                            <input type="password" class="form-control" name="passwordr" id="confirm" placeholder="Confirm your Password" autocomplete="off"
                                           />
                                        </div>
                                    </div>
                                </div>

                                <div class="g-recaptcha"

                                    data-sitekey="6LdKY9MUAAAAAK_4uC2RezTccYY121Km6HNgAjhJ"
                                    
                                    data-size="invisible">

                                </div>

                                <div class="form-group ">

                                    <input type="submit" name="register" class="btn btn-primary btn-lg btn-block login-button" value="Register">
                                    
                                </div>

                                <div class="login-register">
                                    <a href="login.php">Login</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
          </div>

          <script type="text/javascript">
              var unameresults = document.getElementById("unameresults");
              var uname = document.getElementById("uname");

              function getUserNameResults()
              {
                var unameVal = uname.value;

                if(unameVal.length<1)
                {
                    unameresults.style.display='none';
                    return;
                }

                console.log('unameVal : ' +unameVal);
                var xhr = new XMLHttpRequest();
                var url = 'searchusername.php?search=' +unameVal;

                //open function

                xhr.open('GET',url,true);

                xhr.onreadystatechange = function(){

                    if(xhr.readyState == 4 && xhr.status ==200)
                    {
                        var text = xhr.responseText;
                        //console.log('response from searchresult.php : ' +xhr.responseText);
                        unameresults.innerHTML = text;
                        unameresults.style.display = 'block';
                    }
                }

                xhr.send();
              }
              uname.addEventListener("input",getUserNameResults);
          </script>

          <script type="text/javascript">
              var emailresults = document.getElementById("emailresults");
              var email = document.getElementById("email");

              function getEmailResults()
              {
                var emailVal = email.value;

                if(emailVal.length<1)
                {
                    emailresults.style.display='none';
                    return;
                }

                console.log('emailVal : ' + emailVal);
                var xhr = new XMLHttpRequest();
                var url = 'searchemail.php?search=' +emailVal;

                //open function

                xhr.open('GET',url,true);

                xhr.onreadystatechange = function(){

                    if(xhr.readyState == 4 && xhr.status ==200)
                    {
                        var text = xhr.responseText;
                        //console.log('response from searchresult.php : ' +xhr.responseText);
                        emailresults.innerHTML = text;
                        emailresults.style.display = 'block';
                    }
                }

                xhr.send();
              }
              email.addEventListener("input",getEmailResults);
          </script>
  </body>
  </html>