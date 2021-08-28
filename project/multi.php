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

        <title> Login Form</title>
        <!-- Custom CSS Link -->

        <link rel="stylesheet" type="text/css" href="css/multic.css">

        <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
        <link rel="stylesheet" type="text/css" href="css/login.css">
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

      
      </head>
<body>

  <form id="regForm" method= "post">

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

    <h1>Login</h1>

    <!-- One "tab" for each step in the form: -->
    <div class="tab">Username:
     <p><input type="text" placeholder="Username..." oninput="this.className = ''"
      value="<?php if(isset($_POST['email'])){echo $_POST['email'];} ?>" id="email" name="Email"></p>
    </div>

    <div class="tab">      
      <p><input type="text" placeholder="Password..." oninput="this.className = ''" name="Password" id="password"></p>
    </div>

    <div style="overflow:auto;">
      <div style="float:right;">
        <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
        <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button>
      </div>
    </div>

    <div class="forgot">
       <a href="#">Forgot password?</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="register.php">Register Here </a>
    </div>

    <!-- Circles which indicates the steps of the form: -->
    <div style="text-align:center;margin-top:40px;">
      <span class="step"></span>
      <span class="step"></span>
      <span class="step"></span>
      <span class="step"></span>
    </div>

  </form>

  <script type="text/javascript">
      var currentTab = 0; // Current tab is set to be the first tab (0)
      showTab(currentTab); // Display the current tab

  function showTab(n) {
    // This function will display the specified tab of the form ...
    var x = document.getElementsByClassName("tab");
    x[n].style.display = "block";
    // ... and fix the Previous/Next buttons:
    if (n == 0) {
      document.getElementById("prevBtn").style.display = "none";
    } else {
      document.getElementById("prevBtn").style.display = "inline";
    }
    if (n == (x.length - 1)) {
      document.getElementById("nextBtn").innerHTML = "Submit";
    } else {
      document.getElementById("nextBtn").innerHTML = "Next";
    }
    // ... and run a function that displays the correct step indicator:
    fixStepIndicator(n)
  }

  function nextPrev(n) {
    // This function will figure out which tab to display
    var x = document.getElementsByClassName("tab");
    // Exit the function if any field in the current tab is invalid:
    if (n == 1 && !validateForm()) return false;
    // Hide the current tab:
    x[currentTab].style.display = "none";
    // Increase or decrease the current tab by 1:
    currentTab = currentTab + n;
    // if you have reached the end of the form... :
    if (currentTab >= x.length) {
      //...the form gets submitted:
      document.getElementById("regForm").submit();
      return false;
    }
    // Otherwise, display the correct tab:
    showTab(currentTab);
  }

  function validateForm() {
    // This function deals with validation of the form fields
    var x, y, i, valid = true;
    x = document.getElementsByClassName("tab");
    y = x[currentTab].getElementsByTagName("input");
    // A loop that checks every input field in the current tab:
    // for (i = 0; i < y.length; i++) {
    //   // If a field is empty...
    //   if (y[i].value == "") {
    //     // add an "invalid" class to the field:
    //     y[i].className += " invalid";
    //     // and set the current valid status to false:
    //     valid = false;
    //   }
    // }

    // If the valid status is true, mark the step as finished and valid:
    if (valid) {
      document.getElementsByClassName("step")[currentTab].className += " finish";
    }
    return valid; // return the valid status
  }

  // function fixStepIndicator(n) {
  //   // This function removes the "active" class of all steps...
  //   var i, x = document.getElementsByClassName("step");
  //   for (i = 0; i < x.length; i++) {
  //     x[i].className = x[i].className.replace(" active", "");
  //   }
  //   //... and adds the "active" class to the current step:
  //   x[n].className += " active";
  // }
  </script>
</body>
</html>