<?php
namespace Destiny;
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
<body id="login">
<div id="page-wrap">

    <?php $hideSignIn = true; include 'seg/nav.php' ?>

    <section class="container vertical-contain">
        <div style="flex: 1;">

            <h1 class="title">
                <span>Sign in</span>
                <small>with your favourite platform</small>
            </h1>

            <?php if(!empty($this->app)):?>
                <div class="content content-dark clearfix" style="margin: 2rem 0;">
                    <div class="ds-block" style="text-align: center;">
                        <h2><span style="color: #B91010;"><?=Tpl::out($this->app['clientName'])?></span></h2>
                        <h4>Wants to know who you are on Destiny.gg!</h4>
                        <p>They will know your username, id, subscription,<br /> roles, flairs and account creation date.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="content content-dark clearfix">
                <form id="loginform" action="/login" method="post">
                    <input type="hidden" name="follow" value="<?=Tpl::out($this->follow)?>" />
                    <input type="hidden" name="grant" value="<?=Tpl::out($this->grant)?>" />
                    <input type="hidden" name="uuid" value="<?=Tpl::out($this->uuid)?>" />
                    <input type="hidden" name="authProvider" class="hidden" />
                    <div class="ds-block">
                        <div id="loginproviders">
                            <?php foreach (Config::$a['authProfiles'] as $i => $id): ?>
                                <a class="btn btn-lg btn-<?=$id?>" tabindex="<?=$i+1?>" data-provider="<?=$id?>">
                                    <i class="fab fa-<?=$id?>"></i> <?=ucwords($id)?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if($this->grant !== 'code'): ?>
                            <div class="form-group form-group-remember-me">
                                <div class="controls checkbox">
                                    <label>
                                        <input tabindex="1" autofocus type="checkbox" name="rememberme" <?=($this->rememberme) ? 'checked':''?>> Remember me
                                    </label>
                                    <span class="help-block">(this should only be used if you are on a private computer)</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>

</body>
</html>