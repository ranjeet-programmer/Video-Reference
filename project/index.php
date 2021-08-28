<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Homepage</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">

	   <link rel="stylesheet" type="text/css" href="css\style.css">

	    <!-- Google Font Link -->

	     <link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">

	      <!-- Font Awesome CDN -->

  	     <script src="https://kit.fontawesome.com/376a7cdf60.js" crossorigin="anonymous"></script>
  </head>
  <body>

        <section id=header>

            <div class="container-fluid navigation-bar">

                <nav class="navbar navbar-expand-lg">
                  <a href="index.php" class="navbar-brand">VideoReference</a>
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
                          <a href="logout.php" class="nav-link">Logout</a>
                        </li>

                      </ul>
                  </div>

                </nav>
            </div>
        </section>

        <section id="info">

          <div class="row">

            <div class="col-lg-6">

                  <h1 class="bigheading">World Largest Free Educational Videos Center</h1>
                  <button type="button" name="button" class="btn btn-primary btn-lg download-button"> <i class="fab fa-google-play"></i> Download</button>
                  <button type="button" name="button" class="btn  btn-outline-light btn-lg download-button"><i class="fab fa-apple"></i>  Download</button>
            </div>

            <div class="col-lg-6">

                <img src="images/img1.png" alt="Info Image" class="info-image">

            </div>

          </div>

        </section>

        <section id="features">

          <div class="row">

            <div class="col-lg-4 feature-box">
              <i class=" icon fas fa-check-circle fa-4x"></i>
              <h3>Easy to use.</h3>
              <p>So easy to use, user friendly.</p>
            </div>

            <div class="col-lg-4 feature-box">
              <i class="icon fas fa-bullseye fa-4x"></i>
              <h3>Elite Clientele</h3>
              <p>We have all the videos related to the computer field.</p>
            </div>

            <div class="col-lg-4 feature-box">
              <i class="icon fas fa-heart fa-4x"></i>
              <h3>Guaranteed to work.</h3>
              <p>Find the guaranteed high quality videos.</p>
            </div>


          </div>

        </section>

        <section id="videos">

          <div class="row">
              <div class="col-lg-4 video-column">
                <div class="card card1">
                    <div class="card-header">
                        <h3>Java</h3>
                    </div>

                    <div class="card-body">
                      <h2>Free</h2>
                      <p>Complex Conect In Easy Language</p>
                      <p>High Quality Content</p>
                      <p>Absolutely Free</p>

                      <a href="java.php"><button type="button" class="btn btn-lg btn-block  button-color">Go</button></a>
                    </div>
                </div>
              </div>

              <div class="col-lg-4 video-column">
                <div class="card card2">
                    <div class="card-header">
                        <h3>PHP</h3>
                    </div>

                    <div class="card-body">
                      <h2>Free</h2>
                      <p>Complex Conect In Easy Language</p>
                      <p>High Quality Content</p>
                      <p>Absolutely Free</p>

                      <a href="phpvideos.php"><button type="button" class="btn btn-lg btn-block  button-color">Go</button></a>
                    </div>
                </div>
              </div>

              <div class="col-lg-4 video-column">
                <div class="card card3">
                    <div class="card-header">
                        <h3>JavaScript</h3>
                    </div>

                    <div class="card-body">
                      <h2>Free</h2>
                      <p>Complex Conect In Easy Language</p>
                      <p>High Quality Content</p>
                      <p>Absolutely Free</p>

                      <a href="javascript.php"><button type="button" class="btn btn-lg btn-block  button-color">Go</button></a>
                    </div>
                </div>
              </div>
          </div>

        </section>

        <footer id="footer">

          <i class="social-icon fab fa-facebook"></i>
          <i class="social-icon fab fa-instagram"></i>
          <i class="social-icon fab fa-twitter"></i>
          <i class="social-icon far fa-envelope"></i>

          <p>© Copyright 2020  RJSolution</p>
        </footer>


          <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

	         <!-- Popper JS -->
	        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

	         <!-- Latest compiled JavaScript -->
	         <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
  </body>
</html>
