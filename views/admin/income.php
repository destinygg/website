<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta charset="utf-8">
<?php include 'seg/commontop.php' ?>
<link href="<?=Config::cdnv()?>/admin.css" rel="stylesheet" media="screen">
</head>
<body id="admin" class="no-contain">
    <div id="page-wrap">

        <?php include 'seg/top.php' ?>
        <?php include 'seg/admin.nav.php' ?>

        <section class="container">
            <div class="row">
                <div class="col-md-12 col-sm-12 card">
                    <div id="graph4" class="card-inner">
                        <h4></h4>
                        <div class="graph-outer">
                            <canvas height="400"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 card">
                    <div id="graph1" class="card-inner">
                        <div class="graph-outer">
                            <canvas height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 card">
                    <div id="graph2" class="card-inner">
                        <div class="graph-outer">
                            <canvas height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 col-sm-12 card">
                    <div id="graph3" class="card-inner">
                        <div class="graph-outer">
                            <canvas height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container">
            <div class="alert alert-danger" style="margin:0;">
                <strong>Note!</strong>
                Data shown here does NOT take into account fees and taxes.
            </div>
        </section>

    </div>
    
    <?php include 'seg/foot.php' ?>
    <?php include 'seg/commonbottom.php' ?>
    <script src="<?=Config::cdnv()?>/admin.js"></script>


</body>
</html>