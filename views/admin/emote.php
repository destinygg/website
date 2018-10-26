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
        <h3 class="in" data-toggle="collapse" data-target="#emote-content">
            <?php if(empty($this->emote['prefix'])): ?>
                Emote
            <?php else: ?>
                <?=Tpl::out($this->emote['prefix'])?>
            <?php endif; ?>
            <?php if(!empty($this->emote['id'])): ?>
                <small>(<?=Tpl::out($this->emote['id'])?>)</small>
            <?php endif; ?>
        </h3>
        <div id="emote-content" data-id="<?=Tpl::out($this->emote['id'])?>" data-upload="/admin/emotes/upload" data-cdn="<?=Tpl::out(Config::cdnv())?>" class="content content-dark emote-form clearfix collapse in">
            <form id="emote-form" action="<?=$this->action?>" method="post">
                <input name="imageId" type="hidden" value="<?=$this->emote['imageId']?>" />

                <div class="ds-block">
                    <div class="image-view-group">
                        <div class="image-view image-view-primary">
                            <?php if(!empty($this->emote['imageName'])): ?>
                                <img width="<?=Tpl::out($this->emote['width'])?>" height="<?=Tpl::out($this->emote['height'])?>" src="<?=Config::cdnv()?>/emotes/<?=Tpl::out($this->emote['imageName'])?>" />
                            <?php endif; ?>
                        </div>
                        <div class="image-view image-view-add">
                            <i class="fa fa-fw fa-upload fa-3x"></i>
                            <i class="fa fa-fw fa-cog fa-spin fa-3x"></i>
                        </div>
                    </div>

                    <p class="ds-block text-muted">Displayed as ~28x28 icon.</p>

                    <hr style="margin: 2em 0 2em 0;" />

                    <div class="form-group">
                        <label class="control-label" for="inputPrefix">Prefix</label>
                        <div class="controls">
                            <input autocomplete="off" type="text" class="form-control input-lg" name="prefix" id="inputPrefix" value="<?=Tpl::out($this->emote['prefix'])?>" placeholder="Prefix">
                            <span class="help-block">The keyword used to invoke this emote.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Twitch</label>
                        <select class="form-control" name="twitch">
                            <option value="1"<?php if($this->emote['twitch'] == 1):?> selected="selected"<?php endif;?>>Yes</option>
                            <option value="0"<?php if($this->emote['twitch'] == 0):?> selected="selected"<?php endif;?>>No</option>
                        </select>
                        <span class="help-block">If YES only twitch subscribers will be able to use this emote.</span>
                    </div>

                    <div class="form-group">
                        <label>Draft</label>
                        <select class="form-control" name="draft">
                            <option value="1"<?php if($this->emote['draft'] == 1):?> selected="selected"<?php endif;?>>Yes</option>
                            <option value="0"<?php if($this->emote['draft'] == 0):?> selected="selected"<?php endif;?>>No</option>
                        </select>
                        <span class="help-block">If YES, this emote will not be public.</span>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputStyles">Styles</label>
                        <div class="controls">
                            <textarea style="min-height: 10em; min-width: 100%; max-width: 100%; font-family: monospace;" autocomplete="off" class="form-control" name="styles" id="inputStyles" placeholder="Styles ..."><?=Tpl::out($this->emote['styles'])?></textarea>
                            <span class="help-block">Custom CSS that will be applied to the emote.<br />
                            keyword <label>{PREFIX}</label> can be used as a placeholder for the emote prefix.</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="/admin/emotes" class="btn">Cancel</a>
                    <?php if(!empty($this->emote['id'])): ?>
                        <a class="btn btn-danger pull-right delete-emote">Delete</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <form id="delete-form" action="/admin/emotes/<?=Tpl::out($this->emote['id'])?>/delete" method="post"></form>
    <input id="file-input" class="hidden" type="file" name="image" />

</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/web.js"></script>
<script src="<?=Config::cdnv()?>/admin.js"></script>

</body>
</html>