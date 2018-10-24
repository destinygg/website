<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
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
        <h3 class="in" data-toggle="collapse" data-target="#details-content">Twitch</h3>
        <div id="details-content" class="content content-dark clearfix collapse in">
            <div class="ds-block">
                <div class="form-group">
                    <p>
                        <span style="display: block;">Attached profile: <a><?=Tpl::out($this->user['username'])?></a></span>
                        <p class="text-muted">Clicking the authorize button will attempt to grant special permissions.<br />This is for broadcasters only.</p>
                    </p>
                    <div>
                        <a href="/admin/twitch/authorize" class="btn btn-primary" role="button">Authorize</a>
                    </div>
                </div>
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