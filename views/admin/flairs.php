<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="admin" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/alerts.contained.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <div class="content content-dark clearfix">
            <div class="ds-block" style="display: flex;">
                <a href="/admin/flairs/new" class="btn btn-primary">New Flair <i class="fas fa-fw fa-plus"></i></a>
                <input style="margin-left: 1rem;" id="flair-search" type="text" class="form-control" placeholder="Search ..." />
            </div>
        </div>
    </section>

    <section class="container">
        <div id="flair-grid" class="image-grid">
            <?php foreach ($this->flairs as $flair): ?>
                <div data-name="<?=Tpl::out($flair['featureName'])?>" class="image-grid-item <?=($flair['locked'] == 1)?" locked":""?>" data-id="<?=Tpl::out($flair['featureId'])?>" data-imageId="<?=Tpl::out($flair['imageId'])?>">
                    <a style="text-decoration: none;" href="/admin/flairs/<?=Tpl::out($flair['featureId'])?>/edit" class="image-view">
                        <?php if(!boolval($flair['hidden'])): ?>
                            <img alt="<?=Tpl::out($flair['imageName'])?>" width="<?=Tpl::out($flair['width'])?>" height="<?=Tpl::out($flair['height'])?>" src="<?=Config::cdnv()?>/flairs/<?=Tpl::out($flair['imageName'])?>" />
                        <?php else: ?>
                            <i title="Hidden" class="fas fa-fw fa-eye-slash fa-2x"></i>
                        <?php endif; ?>
                    </a>
                    <a href="/admin/flairs/<?=Tpl::out($flair['featureId'])?>/edit" class="image-info" style="<?=(!empty($flair['color'])) ? 'border-color:'.$flair['color']:''?>">
                        <label><?=Tpl::out($flair['featureLabel'])?></label>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('admin.js')?>

</body>
</html>