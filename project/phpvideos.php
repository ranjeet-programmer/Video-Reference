<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Java Videos</title>

    <!-- Bootstrap CDN -->

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">

	<!-- Custom CSS -->

	<link rel="stylesheet" type="text/css" href="css/java.css">

	<!-- Google Font Link -->

	<link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">

	<!-- Font Awesome CDN -->

  	<script src="https://kit.fontawesome.com/376a7cdf60.js" crossorigin="anonymous"></script>

  </head>
  <body>

        <form  action="#" method="post">

          <section id="search-bar">
              <div class="search">
                <nav class="navbar navbar-expand-lg">
                  <a class="navbar-brand" href="phpvideos.php">PHP Videos</a>
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                      <span class="navbar-toggler-icon"></span>
                  </button>

                  <div class="collapse navbar-collapse" id="navbarSupportedContent">

                      <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                          <a href="index.php" class="nav-link">Home</a>
                        </li>

                        <li class="nav-item">
                          <a href="#" class="nav-link">About Us</a>
                        </li>

                        <li class="nav-item">
                          <a href="videos.php" class="nav-link">Videos</a>
                        </li>

                        <li class="nav-item">
                          <a href="#" class="nav-link">Search</a>
                        </li>
                      </ul>
                  </div>
                </nav>

              </div>
          </section>

          <section id="videos">
              <div class="row py-3">

                <?php

                  $con = mysqli_connect("localhost","root","");
                  mysqli_select_db($con,"videoreference");

                  $query =  "SELECT * FROM php";
                  $query_run = mysqli_query($con,$query);

                  $num = mysqli_num_rows($query_run);

                  if($num>0)
                  {
                    while($row = mysqli_fetch_array($query_run))
                    {
                      ?>

                      <div class="col-lg-3 col-md-4 col-sm-6">
                        <form>

                            <div class="card">
                              <h4 style="margin-top:1.5pc" ></h4>
                                <h6 class="card-title bg-info text-white p-2 text-uppercase text-center" >
                                <?php
                                  echo $row['title'];
                                 ?>
                               </h6>

                               <div class="card-body">
                                 <div class="btn-group d-flex">
                                   <form class="form-submit" method="post" action="">
											              <button class="btn btn-success text">
												               <a href="<?php echo  $row['link']; ?>" class="Link">  &nbsp;&nbsp; Link</a>
                                	  </button>
										               </form>
                                 </div>
                               </div>
                            </div>
                        </form>
                      </div>

                      <br><br><br><br><br><br><br>

                      <?php

                    }
                  }

                 ?>
              </div>
          </section>
        </form>
  </body>
</html>
