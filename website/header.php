<?php
// Everything needing the header would need the database anyway.
include_once("database.php");
if (isset($_GET['day']) && strtotime($_GET['day'])) {
	$timestamp = strtotime($_GET['day']);
	$currentday = false;
} else {	
	$timestamp = time();
	$currentday = true;
}
$currentdate = date("Y-m-d", $timestamp);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="32x32" href="./favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="./favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="./favicon-16x16.png">
	<meta property="og:image"
    content="https://robserob.dk/vive/thumb.png" />

    <title>HTC Vive - Estimated shipping</title>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
		
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.0.0/Chart.js"></script>
	
	<style>
	body {
	  padding-top: 70px;
	  padding-bottom: 30px;
	}

	.theme-dropdown .dropdown-menu {
	  position: static;
	  display: block;
	  margin-bottom: 20px;
	}

	.theme-showcase > p > .btn {
	  margin: 5px 0;
	}

	.theme-showcase .navbar .container {
	  width: auto;
	}
	
	.panel {
		width: 30.33333333%;
		float: left;
		margin:0px 10px 10px 10px;
	}

	</style>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body role="document">

    <!-- Fixed navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">HTC Vive estimated shipping</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li <?php if ($currentday && strpos($_SERVER['PHP_SELF'], "index.php")) echo 'class="active"'?>><a href="/vive/">Stats for today</a></li>
            <li <?php if (strpos($_SERVER['PHP_SELF'], "charts.php")) echo 'class="active"'?>><a href="/vive/charts.php">Shipment charts</a></li>
            <li class="dropdown <?php if (!$currentday) echo 'active'?>">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">See stats for past date <span class="caret"></span></a>
              <ul class="dropdown-menu">
				<?php
				$get_days = mysql_query("SELECT COUNT( id ), firstdate FROM  `vive` WHERE  `origin` LIKE  '%Ricany-Jazlovice%' GROUP BY firstdate");
				$day_cnt = 1;
				while ($day = mysql_fetch_array($get_days)) {
					$time = strtotime($day['firstdate']);
					if (!$currentday && $day['firstdate'] == $currentdate) 				
						echo "<li class='active'><a href='/vive/?day=".$day['firstdate']."'>".date("jS F", $time)." <em>(".$day[0].")</em></a></li>";
					else						
						echo "<li><a href='/vive/?day=".$day['firstdate']."'>".date("jS F", $time)." <em>(".$day[0].")</em></a></li>";
					$day_cnt++;				
				}
				?>
              </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container theme-showcase" role="main">