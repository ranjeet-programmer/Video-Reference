<?php 

  session_start();

  require_once('includes/connect.php');
  // echo "Session ID : " .session_id();
  $failmax = 5;

  if(isset($_POST) & !empty($_POST))
  {
    print_r($_POST);

    if(empty($_POST['email']))
    {
        $errors[] = "Username / Email field is required";
    }

    if(empty($_POST['password']))
     {
        $errors[] = "Password field is required";
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
        //select sql query to check the email id exists in database
        //upgrading sql query to work with email and username

            $sql = "SELECT * FROM users WHERE ";
            if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            {
              $sql .= "email=?";
            }
            else
            {
              $sql .= "username=?";
            }

             $result = $db->prepare($sql);
             $result->execute(array($_POST['email']));
             $count = $result->rowCount();
             $res = $result->fetch(PDO::FETCH_ASSOC);

            if($count == 1)
            {
              //Checking Number of Failed LogIn Attempts

                $failsql = "SELECT * FROM login_fail WHERE uid = ? AND loginfailed > NOW() - INTERVAL 60 MINUTE";
                $failresult = $db->prepare($failsql);
                $failresult->execute(array($res['id']));
                $failcount = $failresult->rowCount();

                if($failcount < $failmax)
                {
                  // we will check password and create session

                  //then compairing password with password hash

                  if(password_verify($_POST['password'],$res['password']))
                  {
                    $messages[] = 'Created a Session and Redirect user to Main Page';

                    //Insert Activity into DB Table - user_activity

                     $actsql = "INSERT INTO user_activity(uid,activity) VALUES (:uid, :activity)";
                     $actresult = $db->prepare($actsql);
                     $values = array(
                                       ':uid' => $res['id'],
                                       ':activity' =>'User Logged In'
                                    );

                     $actres = $actresult->execute($values);

                      //update logout time in login_log table , if previous records logout value is blank then insert the logout time

                     // select query to get the record with blank logout time for  the current logged in user

                     $logsql = "SELECT * FROM login_log WHERE uid = ? AND loggedout = '0000-00-00 00:00:00' ORDER BY id DESC LIMIT 1";

                     $logresult = $db->prepare($logsql);
                     $logresult->execute(array($res['id']));
                     $logcount = $logresult->rowCount();
                     $logres = $logresult->fetch(PDO::FETCH_ASSOC);
                     
                     if($logcount ==1)
                     {
                      //update the logout timestap 

                      $logoutsql = "UPDATE login_log SET loggedout = NOW() WHERE id=:id";
                        $logoutresult = $db->prepare($logoutsql);
                        $values = array(
                                            ':id' => $logres['id'],
                                        ) ;

                        $logoutresult->execute($values);
                     }

                    //Insert Login timestamps into DB Table - login_log table

                     $loginsql = "INSERT INTO login_log(uid,loggedin) VALUES (:uid,NOW())";
                     $loginresult = $db->prepare($loginsql);
                     $values = array(
                                       ':uid' => $res['id'],
                                    );
                     $loginres = $loginresult->execute($values);

                     session_regenerate_id();
                     $_SESSION['login'] = true;
                     $_SESSION['id'] = $res['id'];
                     $_SESSION['last_login'] = time();

                     //redirecting users to members area / page

                     header('location:index.php');
                  }
                  else
                  {
                    
                    //Insert Failed Login Attempts to user_activity table

                    $actsql = "INSERT INTO user_activity(uid,activity) VALUES (:uid, :activity)";
                     $actresult = $db->prepare($actsql);
                     $values = array(
                                       ':uid' => $res['id'],
                                       ':activity' =>'User Logged Failed'
                                    );

                     $actres = $actresult->execute($values);


                    //Insert Failed Login Attempts to login_fail table

                     $logfailsql = "INSERT INTO login_fail(uid) VALUES (:uid)";
                     $logfailresult = $db->prepare($logfailsql);
                     $values = array(
                                       ':uid' => $res['id'],
                                    );
                     $logfailres = $logfailresult->execute($values);

                     // calculate the no of remaining attempts

                     $remainingattempts = $failmax - $failcount;


                     $errors[] = "Username or Password is Incorrect";
                     $errors[] = "you have $remainingattempts login attempts remaining  , otherwise you will be block for the 60 minutes";

                  }

                }
                else
                {
                  $errors[] = "You Are Blocked for 60 minutes to login , retry after some time";
                }
            }
            else
            {
              $errors[] = "Username / Email is Not Valid";
            }
      }
  }

  //create CSRF Token

    $token = md5(uniqid(rand(),TRUE));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();

 ?>
<html>
  <head>

  <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
  <link rel="stylesheet" type="text/css" href="css/login.css">
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

  </head>

<body id="LoginForm">

<div class="container">
  
  <div class="login-form">

    <div class="main-div">

        <div class="panel">
           <h2>Admin Login</h2>
           <p>Please enter your email and password</p>
        </div>

        <form id="Login" method="post" role="form">

                          <?php

                              // echo "Session ID : " .session_id();
                              // echo "<pre>";
                              //   print_r($_SESSION);
                              // echo "</pre>";

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

              <input type="hidden" name="csrf_token" value="<?php echo $token; ?>"> 
            <div class="form-group">

                <input type="text" class="form-control" id="email" placeholder="Email Address" name="email"
                 value="<?php if(isset($_POST['email'])){echo $_POST['email'];} ?>">

            </div>

            <div class="form-group">

                <input type="password" class="form-control" id="password" placeholder="Password" name="password">

            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox" name="remember" value="Remember Me"> Remember Me
              </label>
            </div>

            <div class="forgot">
              <a href="#">Forgot password?</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <a href="register.php">Register Here </a>
            </div>

            
            <input type="submit" class="btn btn-primary" value="Login" name="login" >

        </form>
     </div>
</div>
</div>
</div>


</body>
</html>