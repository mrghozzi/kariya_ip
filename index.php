<?php
   if( ! ini_get('date.timezone') )
{
    date_default_timezone_set('GMT');
}

if(!empty($_GET["ip"]))
 {
  $s = str_replace(array('http://','https://','/'), '' , $_GET["ip"]);
  $ipppir = $s;
 }else{
  $ipppir =  $_SERVER['REMOTE_ADDR'];
}

$queipy = @unserialize(file_get_contents('http://ip-api.com/php/'.$ipppir));
if($queipy && $queipy['status'] == 'success') {
  function g_contriy($name){
    global $queipy ;
    echo $queipy["{$name}"];

  }
  $olgging =  $queipy['countryCode'];
  $negging = strtolower($olgging);
} else {
  $msg = "Unable to get location";
  $m_err="1";
}

if($m_err=="1")
 {
   echo $msg;
   echo "<meta http-equiv='refresh' content='5;url=index.php'>";
   exit;
 }

?>
<!--
Author: kariya host
Author URL: http://www.krhost.ga
Design  : w3layouts
License: Creative Commons Attribution 3.0 Unported
License URL: http://creativecommons.org/licenses/by/3.0/
-->
<!DOCTYPE html>
<html>
<head>
<title>Free Geolocation API :: By Kariya Host</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="Profile Contact Form template Responsive, Login form web template,Flat Pricing tables,Flat Drop downs  Sign up Web Templates, Flat Web Templates, Login sign up Responsive web template, SmartPhone Compatible web template, free web designs for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<!-- Custom Theme files -->
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="js/bootstrap.js"></script>
<!-- //Custom Theme files -->
<!-- web font -->
<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
<link href="//fonts.googleapis.com/css?family=Quicksand:300,400,500,700" rel="stylesheet">
<!-- //web font -->
</head>
<body>
    <nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">

      <a class="navbar-brand"  href="index.php"><img src="logo.png" alt="Brand"  width="90" height="35" alt="" /></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div  id="bs-example-navbar-collapse-1">
     <form class="navbar-form navbar-left" action="index.php" method="GET">
        <div class="form-group">
          Query IP/domain
          <input type="text" name="ip" class="form-control" value="<?php g_contriy('query'); ?>" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav
	<!-- main -->
	<div class="main">

		<div class="main-wthree-row">

			<div class="agileits-info">
				<div class="agileits-infoleft">
					<img src="png/<?php echo $negging;  ?>.png" alt=""/>
					<div class="agileits-infotext">
						<h2><?php g_contriy('country'); ?></h2>
						<h6><?php g_contriy('city'); ?></h6>
					</div>
				</div>
				<div class="agileits-inforight">
					<p><img src="images/i1.png" alt=""><?php g_contriy('query'); ?></p>
					<p><img src="images/i2.png" alt=""><?php g_contriy('timezone'); ?></p>
				</div>
				<div class="clear"> </div>
			</div>

			<div class="contact-wthree">
               <div class="panel panel-default">
  <!-- Default panel contents -->
  <div class="panel-heading">Query result</div>

			   <table class="table" ><tbody id="o">
               <tr><th>IP:</th><td><span id="qr"><a href="index.php?ip=<?php g_contriy('query'); ?>"><?php g_contriy('query'); ?></a></span></td></tr>
               <tr><th>Country:</th><td><span><?php g_contriy('country'); ?></span></td></tr>
               <tr><th>Country code:</th><td><span><?php g_contriy('countryCode'); ?></span></td></tr>
               <tr><th>Region:</th><td><span><?php g_contriy('regionName'); ?></span></td></tr>
               <tr><th>Region code:</th><td><span><?php g_contriy('region'); ?></span></td></tr>
               <tr><th>City:</th><td><span><?php g_contriy('city'); ?></span></td></tr>
               <tr><th>Zip Code:</th><td><span><?php g_contriy('zip'); ?></span></td></tr>
               <tr><th>Latitude:</th><td><span><?php g_contriy('lat'); ?></span></td></tr>
               <tr><th>Longitude:</th><td><span><?php g_contriy('lon'); ?></span></td></tr>
               <tr><th>Timezone:</th><td><span><?php g_contriy('timezone'); ?></span></td></tr>
               <tr><th>ISP:</th><td><span><?php g_contriy('isp'); ?></span></td></tr>
               <tr><th>Organization:</th><td><span><?php g_contriy('org'); ?></span></td></tr>
               <tr><th>AS number/name:</th><td><span><?php g_contriy('as'); ?></span></td></tr>
               </tbody></table>
               </div>
			</div>
		</div>
	</div>
	<!-- //main -->
	<!-- copyright -->
	<div class="w3copyright-agile">
		<p>© 2014 - <?php echo date("Y")  ?> All rights reserved <a href="http://www.krhost.ga" target="_blank"> Kariya host</a> ::&nbsp;<a href="http://ip-api.com" target="_blank">ip-api</a> </p>
	</div>
	<!-- //copyright -->

</body>
</html>
