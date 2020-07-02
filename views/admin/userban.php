<?php
use Destiny\Common\Utils\Tpl;
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
        <h3>Ban <small>(<a href="/admin/user/<?=Tpl::out($this->user['userId'])?>/edit"><?=Tpl::out($this->user['username'])?></a>)</small></h3>
        <div class="content content-dark clearfix">

            <?php
            if(!empty($this->ban['id'])):
                $href='/admin/user/'. Tpl::out($this->user['userId']) .'/ban/update';
            else:
                $href='/admin/user/'. Tpl::out($this->user['userId']) .'/ban';
            endif;
            ?>

            <form action="<?=$href?>" method="post">

                <div class="ds-block">
                    <div class="form-group">
                        <label class="control-label" for="inputUsername">Banned user</label>
                        <div class="controls">
                            <input type="text" class="form-control uneditable-input" readonly="readonly" value="<?=Tpl::out($this->user['username'])?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputUsername">Reason</label>
                        <div class="controls">
                            <input type="text" class="form-control" name="reason" id="inputReason" value="<?=Tpl::out($this->ban['reason'])?>" placeholder="Reason">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputStarttimestamp">Start</label>
                        <div class="controls">
                            <input type="text" class="form-control" name="starttimestamp" id="inputStarttimestamp" value="<?=Tpl::out($this->ban['starttimestamp'])?>" placeholder="Y-m-d H:i:s">
                            <span class="help-block">time specified in UCT</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputEndtimestamp">End</label>
                        <div class="controls">
                            <input type="text" class="form-control" name="endtimestamp" id="inputEndtimestamp" value="<?=Tpl::out($this->ban['endtimestamp'])?>" placeholder="Y-m-d H:i:s">
                            <span class="help-block">time specified in UCT</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">Save</button>
                    <a href="/admin/user/<?=Tpl::out($this->user['userId'])?>/edit" class="btn btn-dark">Back</a>
                </div>

            </form>
        </div>
    </section>

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