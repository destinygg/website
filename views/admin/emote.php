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
        <div id="emote-content" data-id="<?=$this->emote['id']?>" class="content content-dark emote-form collapse show">
            <form id="emote-form" action="<?=$this->action?>" method="post">

                <div class="ds-block">

                    <div class="image-view-group">
                        <div class="image-view image-view-upload" data-upload="/admin/emotes/upload" data-cdn="<?=Tpl::out(Config::cdnv())?>/emotes/">
                            <input id="inputImage" name="imageId" type="hidden" value="<?=$this->emote['imageId']?>" />
                            <?php if(!empty($this->emote['imageName'])): ?>
                                <img class="is-loading" alt="<?=Tpl::out($this->emote['imageName'])?>" width="<?=Tpl::out($this->emote['width'])?>" height="<?=Tpl::out($this->emote['height'])?>" src="<?=Config::cdnv()?>/img/image-bad.svg" data-src="<?=Config::cdnv()?>/emotes/<?=Tpl::out($this->emote['imageName'])?>" />
                            <?php else: ?>
                                <i class="fas fa-fw fa-upload fa-3x"></i>
                            <?php endif; ?>
                            <i class="fas fa-fw fa-cog fa-spin fa-3x"></i>
                        </div>
                    </div>

                    <p class="ds-block text-muted">
                        <?php if(!empty($this->emote['imageName'])): ?>
                        Image size <?=Tpl::out($this->emote['width'])?> x <?=Tpl::out($this->emote['height'])?><br />
                        <?php endif; ?>
                        Ideally a ~28x28 image.
                    </p>

                    <hr style="margin: 2em 0 2em 0;" />

                    <div class="form-group">
                        <label for="inputTheme">Theme</label>
                        <select class="form-control" name="theme" id="inputTheme">
                            <?php foreach($this->themes as $theme): ?>
                                <option value="<?=$theme['id']?>"<?php if($theme['id'] == $this->emote['theme']):?> selected="selected"<?php endif;?>>
                                    <?=$theme['label']?> <?php if(boolval($theme['active'])): ?>(active)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-block">Which theme this emote belongs to.</span>

                        <div class="mt-3 mb-4 d-flex align-items-center">
                            <div class="mr-2"><button data-toggle="modal" data-target="#emoteCopyModal" type="button" class="btn btn-info">Copy <i class="fas fa-copy"></i></button></div>
                            <div><span class="help-block flex-fill">copy this emote into another theme</span></div>
                        </div>

                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputPrefix">Prefix</label>
                        <div class="controls">
                            <input autocomplete="off" type="text" class="form-control input-lg" name="prefix" id="inputPrefix" value="<?=Tpl::out($this->emote['prefix'])?>" placeholder="Prefix">
                            <span class="help-block">The keyword used to invoke this emote.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputTwitch">Twitch</label>
                        <select class="form-control" name="twitch" id="inputTwitch">
                            <option value="1"<?php if($this->emote['twitch'] == 1):?> selected="selected"<?php endif;?>>Yes</option>
                            <option value="0"<?php if($this->emote['twitch'] == 0):?> selected="selected"<?php endif;?>>No</option>
                        </select>
                        <span class="help-block">If YES only twitch subscribers will be able to use this emote.</span>
                    </div>

                    <div class="form-group">
                        <label for="inputDraft">Draft</label>
                        <select class="form-control" name="draft" id="inputDraft">
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
                    <button id="emoteEditPreviewBtn" data-id="<?=$this->emote['id']?>" type="button" class="btn btn-success">Preview</button>
                    <a href="/admin/emotes" class="btn btn-dark">Cancel</a>
                    <?php if(!empty($this->emote['id'])): ?>
                        <button type="button" class="btn btn-danger float-right delete-item">Delete</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <form id="delete-form" action="/admin/emotes/<?=Tpl::out($this->emote['id'])?>/delete" method="post"></form>
    <input id="file-input" class="hidden" type="file" name="image" />

</div>

<div class="modal fade" id="emoteCopyModal" tabindex="-1" role="dialog" aria-labelledby="emoteCopyModalTitle" aria-hidden="true">
    <form action="/admin/emotes/<?=$this->emote['id']?>/copy" method="post">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="emoteCopyModalTitle">Which theme would you like to copy to?</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <select class="form-control" name="theme" id="inputTheme">
                            <?php foreach($this->themes as $theme): ?>
                                <?php if($theme['id'] != $this->emote['theme']):?>
                                    <option value="<?=$theme['id']?>"><?=$theme['label']?> <?php if(boolval($theme['active'])): ?>(active)<?php endif; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="copyEmoteConfirmBtn">Copy</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="emotePreviewModal" tabindex="-1" role="dialog" aria-labelledby="emotePreviewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
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