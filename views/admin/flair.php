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
        <h3 class="in" data-toggle="collapse" data-target="#flair-content">
            <?php if(empty($this->flair['featureLabel'])): ?>
                Flair
            <?php else: ?>
                <?=Tpl::out($this->flair['featureLabel'])?>
            <?php endif; ?>
            <?php if(!empty($this->flair['featureId'])): ?>
                <small>(<?=Tpl::out($this->flair['featureId'])?>)</small>
            <?php endif; ?>
        </h3>
        <div id="flair-content" data-id="<?=Tpl::out($this->flair['featureId'])?>" class="content content-dark emote-form collapse show">
            <form id="emote-form" action="<?=$this->action?>" method="post">

                <div class="ds-block">

                    <div class="image-view-group">
                        <div class="image-view image-view-upload" data-upload="/admin/flairs/upload" data-cdn="<?=Tpl::out(Config::cdnv())?>/flairs/">
                            <?php if(!empty($this->flair['imageName'])): ?>
                                <img alt="<?=Tpl::out($this->flair['imageName'])?>" width="<?=Tpl::out($this->flair['width'])?>" height="<?=Tpl::out($this->flair['height'])?>" src="<?=Config::cdnv()?>/flairs/<?=Tpl::out($this->flair['imageName'])?>" />
                            <?php else: ?>
                                <i class="fas fa-fw fa-upload fa-3x"></i>
                            <?php endif; ?>
                            <i class="fas fa-fw fa-cog fa-spin fa-3x"></i>
                            <input name="imageId" type="hidden" value="<?=$this->flair['imageId']?>" />
                        </div>
                    </div>


                    <p class="ds-block text-muted">
                        <?php if(!empty($this->emote['imageName'])): ?>
                            Image size <?=Tpl::out($this->emote['width'])?> x <?=Tpl::out($this->emote['height'])?><br />
                        <?php endif; ?>
                        Ideally a 16x16 or 18x18 image.
                    </p>

                    <hr style="margin: 2em 0 2em 0;" />

                    <div class="form-group">
                        <label class="control-label" for="inputFeatureLabel">Label</label>
                        <div class="controls">
                            <input autocomplete="off" type="text" class="form-control input-lg" name="featureLabel" id="inputFeatureLabel" value="<?=Tpl::out($this->flair['featureLabel'])?>" placeholder="Label">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputFeatureName">Identifier</label>
                        <div class="controls">
                            <?php if(empty($this->flair['featureName'])): ?>
                                <select class="form-control" name="featureName" <?=($this->flair['locked'] == 1)?'disabled="disabled"':''?>>
                                    <?php foreach($this->presets as $preset): ?>
                                        <option value="<?=$preset?>"<?php if($this->flair['featureName'] == $preset):?> selected="selected"<?php endif;?>><?=$preset?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input autocomplete="off" type="text" class="form-control" name="featureName" id="inputFeatureName" value="<?=Tpl::out($this->flair['featureName'])?>" placeholder="Prefix" disabled="disabled">
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if(empty($this->flair['featureId'])): ?>
                        <div class="form-group">
                            <label>Locked</label>
                            <select class="form-control" name="locked" <?=($this->flair['locked'] == 1)?'disabled="disabled"':''?>>
                                <option value="1"<?php if($this->flair['locked'] == 1):?> selected="selected"<?php endif;?>>Yes</option>
                                <option value="0"<?php if($this->flair['locked'] == 0):?> selected="selected"<?php endif;?>>No</option>
                            </select>
                            <span class="help-block">If YES, this flair cannot be deleted.</span>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Hide Icon</label>
                        <select class="form-control" name="hidden">
                            <option value="1"<?php if($this->flair['hidden'] == 1):?> selected="selected"<?php endif;?>>Yes</option>
                            <option value="0"<?php if($this->flair['hidden'] == 0):?> selected="selected"<?php endif;?>>No</option>
                        </select>
                        <span class="help-block">If YES, this flair <strong>icon</strong> is not displayed in the chat.</span>
                    </div>

                    <div class="form-group">
                        <label>Priority</label>
                        <input autocomplete="off" type="text" class="form-control" name="priority" id="inputPriorityLabel" value="<?=Tpl::out($this->flair['priority'])?>" placeholder="Priority">
                        <span class="help-block">Which flair gets displayed in what order.</span>
                    </div>

                    <div class="form-group color-group">
                        <label>Color</label>
                        <div class="input-group color-select">
                            <div style="background-color: <?=Tpl::out($this->flair['color'])?>; border-color: <?=Tpl::out($this->flair['color'])?>;" class="input-group-addon">&nbsp;</div>
                            <input style="font-weight: 600;" autocomplete="off" type="text" class="form-control" name="color" id="inputColor" value="<?=Tpl::out($this->flair['color'])?>" placeholder="Color hex e.g. #FF0000">
                        </div>
                        <span class="help-block">The color applied to the username in chat.</span>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="/admin/flairs" class="btn">Cancel</a>
                    <?php if(!empty($this->flair['featureId']) && $this->flair['locked'] == 0): ?>
                        <a class="btn btn-danger float-right delete-item">Delete</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <form id="delete-form" action="/admin/flairs/<?=Tpl::out($this->flair['featureId'])?>/delete" method="post"></form>
    <input id="file-input" class="hidden" type="file" name="image" />

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