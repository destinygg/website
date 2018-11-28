<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Country;
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
<body id="register" class="no-brand">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/banner.php' ?>

    <section class="container">

        <h1 class="title">
            <span>Confirm</span>
            <small>your sign-up details</small>
        </h1>

        <?php if(!empty($this->error)): ?>
            <div class="alert alert-danger">
                <strong>Error!</strong>
                <?=Tpl::out($this->error->getMessage())?>
            </div>
        <?php endif ?>

        <div class="content content-dark clearfix">
            <form action="/register" method="post">
                <input type="hidden" name="code" value="<?=Tpl::out($this->code)?>" />
                <input type="hidden" name="grant" value="<?=Tpl::out($this->grant)?>" />
                <input type="hidden" name="follow" value="<?=Tpl::out($this->follow)?>" />
                <input type="hidden" name="uuid" value="<?=Tpl::out($this->uuid)?>" />
                <div class="ds-block">
                    <div class="form-group">
                        <label class="control-label" for="inputUsername">Username / Nickname</label>
                        <div class="controls">
                            <input type="text" class="form-control input-lg" name="username" id="inputUsername" value="<?=Tpl::out($this->username)?>" placeholder="Username">
                            <span class="help-block">A-z 0-9 and underscores. Must contain at least 3 and at most 20 characters</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls checkbox">
                            <label>
                                <input type="checkbox" name="rememberme" <?=($this->rememberme) ? 'checked':''?>> Remember me
                            </label>
                            <span class="help-block">(this should only be used if you are on a private computer)</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <div class="g-recaptcha" data-sitekey="<?=Config::$a['g-recaptcha']['key']?>"></div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">Continue</button>
                    <a href="/login?grant=<?=urlencode($this->grant)?>&uuid=<?=urlencode($this->uuid)?>&follow=<?=urlencode($this->follow)?>" class="btn btn-lg">Cancel</a>
                </div>
            </form>
        </div>

        <br />
        <p class="agreement" style="text-align: center;">By clicking the &quot;Continue&quot; button, you are confirming that you have read and agree with the <a href="/agreement">user agreement</a>.</p>

    </section>
</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<script src="https://www.google.com/recaptcha/api.js"></script>

</body>
</html>