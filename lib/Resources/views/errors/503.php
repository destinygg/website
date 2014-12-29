<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
$words = include 'words.php';
$word = $words [array_rand ( $words, 1 )];
?>
<!DOCTYPE html>
<html>
<head>
<title>Maintenance - Destiny</title>
<meta charset="utf-8">
<link href="<?=Config::cdnv()?>/errors/css/style.min.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body class="error logicerror">
    <div id="page-wrap">
        <div id="middle-wrap">
            <?php include'top.php' ?>
            <section id="header-band">
                <div class="container">
                    <div id="overview">
                        <div id="illustration"></div>
                        <div id="info">
                            <h1><strong><?=$word?>!</strong> Maintenance</h1>
                            <p>We are doing some work on the site. We will be back shortly</p>
                        </div>
                    </div>
                </div>
            </section>
            <?php include'foot.php' ?>
        </div>
    </div>
</body>
</html>