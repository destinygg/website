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
        <div class="content content-dark clearfix">
            <div class="ds-block" style="display: flex;">
                <a href="/admin/flairs/new" class="btn btn-primary">New Flair <i class="fa fa-fw fa-plus"></i></a>
                <!--<input style="margin-left: 1rem;" id="emote-search" type="text" class="form-control" placeholder="Search ..." />-->
            </div>
        </div>
    </section>

    <section class="container">
        <div id="uploaded-container" class="image-grid">
            <?php foreach ($this->flairs as $flair): ?>
                <a href="/admin/flairs/<?=Tpl::out($flair['featureId'])?>/edit" class="image-grid-item <?=($flair['locked'] == 1)?" locked":""?>" data-id="<?=Tpl::out($flair['featureId'])?>" data-imageId="<?=Tpl::out($flair['imageId'])?>">
                    <div class="image-view">
                        <img width="<?=Tpl::out($flair['width'])?>" height="<?=Tpl::out($flair['height'])?>" src="<?=Config::cdnv()?>/flairs/<?=Tpl::out($flair['imageName'])?>" />
                    </div>
                    <div class="image-info">
                        <?php if(!empty($flair['featureLabel'])): ?>
                            <label><?=Tpl::out($flair['featureLabel'])?></label>
                        <?php else: ?>
                            <label class="not-set">NOT SET</label>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/web.js"></script>
<script src="<?=Config::cdnv()?>/admin.js"></script>

</body>
</html>