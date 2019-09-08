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
    <link href="<?=Config::cdnv()?>/emotes/emotes.css?_=<?=Tpl::out($this->cacheKey)?>" rel="stylesheet" media="screen">
</head>
<body id="admin" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <div class="content content-dark filter-form">
            <div class="form-inline filters">
                <div class="form-group" style="flex: 1; display: flex;">
                    <a href="/admin/emotes/new" class="btn btn-primary">Emote <i class="fas fa-fw fa-plus"></i></a>
                    <input style="margin-left: 1rem; flex: 1;" id="emote-search" type="text" class="form-control" placeholder="Search ..." />
                </div>
                <div class="form-group">
                    <label class="mr-2">Theme</label>
                    <select class="form-control" id="themeSelect" name="theme" style="flex: 1;">
                        <?php foreach($this->themes as $theme): ?>
                            <option value="<?=$theme['id']?>"<?php if($theme['id'] == $this->theme['id']):?> selected="selected"<?php endif;?>>
                                <?=$theme['label']?>
                                <?php if(boolval($theme['active'])): ?>
                                    (active)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <section class="container">
        <div id="emote-grid" class="image-grid">
            <?php foreach ($this->emotes as $emote): ?>
                <div data-prefix="<?=Tpl::out($emote['prefix'])?>" class="image-grid-item <?=($emote['twitch'] == 1)?"twitch":""?> <?=($emote['draft'] == 1)?" draft":""?>" data-id="<?=Tpl::out($emote['id'])?>" data-imageId="<?=Tpl::out($emote['imageId'])?>">
                    <div class="image-view" style="position: relative;">
                        <?php if(!empty($emote['imageName'])): ?>
                            <a href="/admin/emotes/<?=$emote['id']?>/edit" title="<?=Tpl::out($emote['prefix'])?>">
                                <img class="is-loading" alt="<?=Tpl::out($emote['imageName'])?>" width="<?=Tpl::out($emote['width'])?>" height="<?=Tpl::out($emote['height'])?>" src="<?=Config::cdnv()?>/img/image-bad.svg" data-src="<?=Config::cdnv()?>/emotes/<?=Tpl::out($emote['imageName'])?>" />
                            </a>
                            <div title="Preview" class="preview-icon m-1" data-id="<?=$emote['id']?>">
                                <i class="fas fa-eye"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php $style = (!empty($this->theme) && $emote['theme'] == $this->theme['id']) ? "border-color: ". ($this->theme['color'] ?? "black") .";" : ""; ?>
                    <a href="/admin/emotes/<?=$emote['id']?>/edit" class="image-info" style="<?=$style?>">
                        <label><?=Tpl::out($emote['prefix'])?></label>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

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