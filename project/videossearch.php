<?php

    if(isset($_POST['search']))
    {
          $valueToSearch = $_POST['valueToSearch'];
          $query = "SELECT * FROM `videos` WHERE `title` LIKE '%".$valueToSearch."%'";
          $search_result = filterTable($query);
    }
    else {

          $query = "SELECT * FROM `videos`";
          $search_result = filterTable($query);
    }

    function filterTable($query)
    {
      $connect = mysqli_connect("localhost","root","","videoreference");
      $filter_result = mysqli_query($connect,$query);
      return $filter_result;
    }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>JavaScript Search Videos</title>

    <!-- Bootstrap CDN-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">

    <!-- Custom CSS -->

  	<link rel="stylesheet" type="text/css" href="css/java.css">

  	<!-- Google Font Link -->

  	<link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">

  	<!-- Font Awesome CDN -->

    	<script src="https://kit.fontawesome.com/376a7cdf60.js" crossorigin="anonymous"></script>
  </head>
  <body>


      <form method="post">

        <section id="search-bar">
            <div class="search">
              <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="java.php"> Videos</a>
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
                    </ul>
                </div>
              </nav>

            </div>
        </section>


        <div class="container">


          <input type="text" name="valueToSearch" placeholder="Value To Search" autocomplete="off">
          <input type="submit" name="search" value="Filter"><br /><br />
          <table class="table table-hover">

            <thead>
              <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Link</th>
              </tr>
            </thead>

            <?php while($row = mysqli_fetch_array($search_result)):?>
              <tbody>
                    <tr>
                      <td><?php echo $row['id']; ?></td>
                      <td><?php  echo $row['title'];?></td>
                      <td><a href="<?php  echo $row['link'];?>">Link</a></td>
                    </tr>
              </tbody>
            <?php  endwhile;?>

        </table>
        </div>
      </form>
  </body>
</html>
