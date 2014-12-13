<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
$words = include 'errors/words.php';
$word = $words [array_rand ( $words, 1 )];
?>
<!DOCTYPE html>
<html>
<head>
<title>Maintenance</title>
<meta charset="utf-8">
<link href="<?=Config::cdn()?>/vendor/bootstrap-3.1.1/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/vendor/font-awesome-4.2.0/css/font-awesome.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/errors/css/style.min.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body class="error logicerror">

  <?php include'errors/top.php'?>

  <section id="header-band">
    <div class="container">
      <div id="overview">
        <div class="clearfix">
          <h1><strong><?=$word?>!</strong> Maintenance</h1>
          <p>
            The website is down for maintenance. We'll be right back
          </p>
        </div>
        <div id="destiny-illustration"></div>
      </div>
    </div>
  </section>
  
  <?php include'errors/foot.php'?>
  
  <script src="<?=Config::cdn()?>/vendor/js/jquery-1.10.2.min.js"></script>
  <script src="<?=Config::cdn()?>/vendor/js/bootstrap.js"></script>

</body>
</html>