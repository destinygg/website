<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?=Tpl::title($this->title)?></title>
    <?php include 'seg/meta.php' ?>
    <link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="admin" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/alerts.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <h3 class="in" data-toggle="collapse" data-target="#admin-dashboard-content">Dashboard</h3>
        <div id="admin-dashboard-content" class="content content-dark collapse in clearfix">
            <div class="ds-block">
                <p>If you are seeing this, you only have access to the <code>ADMIN</code> role.<br /></p>
                <p>Work in progress ...</p>
            </div>
        </div>
    </section>

</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/web.js"></script>
<script src="<?=Config::cdnv()?>/admin.js"></script>

</body>
</html>