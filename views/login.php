<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('common.vendor.css')?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="login" class="no-brand">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>

    <section class="container">

        <h1 class="title">
            <span>Sign in</span>
            <small>with your favourite platform</small>
        </h1>

        <?php if(!empty($this->error)): ?>
            <div class="alert alert-danger">
                <strong>Error!</strong>
                <?=Tpl::out($this->error->getMessage())?>
            </div>
        <?php endif ?>

        <div class="content content-dark clearfix">
            <form id="loginform" action="/login" method="post">
                <input type="hidden" name="follow" value="<?=Tpl::out($this->follow)?>" />
                <input type="hidden" name="authProvider" class="hidden" />
                <div class="ds-block">
                    <div class="form-group">
                        <div class="controls checkbox">
                            <label>
                                <input tabindex="1" autofocus type="checkbox" name="rememberme" <?=($this->rememberme) ? 'checked':''?>> Remember me
                            </label>
                            <span class="help-block">(this should only be used if you are on a private computer)</span>
                        </div>
                    </div>
                    <div id="loginproviders">
                        <?php foreach (Config::$a['authProfiles'] as $i => $id): ?>
                            <a class="btn btn-lg btn-<?=$id?>" tabindex="<?=$i+1?>" data-provider="<?=$id?>">
                                <i class="fa fa-<?=$id?>"></i> <?=ucwords($id)?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>

</body>
</html>