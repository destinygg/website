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
        <h3>Twitch Integration</h3>
        <div class="content content-dark clearfix">
            <div class="ds-block">
                <div class="form-group">
                    <p>
                        <span style="display: block;">Broadcaster profile: <a><?=Tpl::out($this->user['username'])?></a></span>
                        <span class="text-muted">Clicking the authorize button will attempt to grant special permissions. This is for broadcasters only.</span>
                    </p>
                    <div>
                        <a href="/admin/twitch/authorize" class="btn btn-danger" role="button">Authorize</a>
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