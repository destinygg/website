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
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <h3 class="in" data-toggle="collapse" data-target="#theme-content">
            <?php if(empty($this->theme['label'])): ?>
                Theme
            <?php else: ?>
                <?=Tpl::out($this->theme['label'])?>
            <?php endif; ?>
            <?php if(!empty($this->theme['id'])): ?>
                <small>(<?=Tpl::out($this->theme['id'])?>)</small>
            <?php endif; ?>
        </h3>
        <div id="theme-content" data-id="<?=Tpl::out($this->theme['id'])?>" class="content content-dark theme-form collapse show">
            <form id="theme-form" action="<?=$this->action?>" method="post">

                <div class="ds-block">

                    <div class="form-group">
                        <label class="control-label" for="inputPrefix">Prefix</label>
                        <div class="controls">
                            <input autocomplete="off" type="text" class="form-control input-lg" name="prefix" id="inputPrefix" value="<?=Tpl::out($this->theme['prefix'])?>" placeholder="Prefix">
                            <span class="help-block">The string used within the css. Letters only. e.g. xmas, halloween</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputLabel">Label</label>
                        <div class="controls">
                            <input autocomplete="off" type="text" class="form-control input-lg" name="label" id="inputLabel" value="<?=Tpl::out($this->theme['label'])?>" placeholder="Label">
                            <span class="help-block">A unique label.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Active</label>
                        <select class="form-control" name="active">
                            <option value="1"<?php if($this->theme['active'] == 1):?> selected="selected"<?php endif;?>>Yes</option>
                            <option value="0"<?php if($this->theme['active'] == 0):?> selected="selected"<?php endif;?>>No</option>
                        </select>
                        <span class="help-block">Only one theme is active at a time, setting this to YES will unset any other theme to NO. If all themes are not active, the default theme is used.</span>
                    </div>

                    <div class="form-group color-group">
                        <label>Color</label>
                        <div class="input-group color-select">
                            <div style="background-color: <?=Tpl::out($this->theme['color'])?>; border-color: <?=Tpl::out($this->theme['color'])?>;" class="input-group-addon">&nbsp;</div>
                            <input style="font-weight: 600;" autocomplete="off" type="text" class="form-control" name="color" id="inputColor" value="<?=Tpl::out($this->theme['color'])?>" placeholder="Color hex e.g. #FF0000">
                        </div>
                        <span class="help-block">Something to identify this theme.</span>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="/admin/themes" class="btn btn-dark">Cancel</a>
                    <?php if(!empty($this->theme['id'])): ?>
                        <button type="button" class="btn btn-danger float-right delete-item">Delete</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <form id="delete-form" action="/admin/themes/<?=$this->theme['id']?>/delete" method="post"></form>

</div>

<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('admin.js')?>

</body>
</html>