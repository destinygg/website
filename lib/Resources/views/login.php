<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="login">
  <div id="page-wrap">

    <?php include Tpl::file('seg/top.php') ?>
    <?php include Tpl::file('seg/headerband.php') ?>
    
    <section class="container">
    
      <h1 class="title">
        <span>Login</span>
        <small>with your preferred login method</small>
      </h1>
      
      <?php if(!empty($model->error)): ?>
      <div class="alert alert-error">
        <strong>Error!</strong>
        <?=Tpl::out($model->error->getMessage())?>
      </div>
      <?php endif; ?>
      
      <div class="content content-dark clearfix">

        <div class="ds-block">
          <p>No private information will ever be shown on the website. This excludes the custom destiny.gg username you specify.</p>
        </div>

        <form id="loginForm" action="/login" method="post">
          <input type="hidden" name="follow" value="<?=Tpl::out($model->follow)?>" />
          <div class="ds-block">

            <div class="form-group">
              <div class="controls">
                <label class="checkbox">
                  <input type="checkbox" name="rememberme" <?=($model->rememberme) ? 'checked':''?>> Remember my login
                </label>
                <span class="help-block">(this should only be used if you are on a private computer)</span>
              </div>
            </div>
            
            <div class="form-group">
              <h3>Login with ...</h3>
            </div>
            
            <div class="form-group">
              <label class="radio">
                <input type="radio" name="authProvider" value="twitch">
                <i class="icon-twitch"></i> Twitch
              </label>
            </div>
            <div class="form-group">
              <label class="radio">
                <input type="radio" name="authProvider" value="reddit">
                <i class="icon-reddit"></i> Reddit
              </label>
            </div>
            <div class="form-group">
              <label class="radio">
                <input type="radio" name="authProvider" value="google">
                <i class="icon-google"></i> Google
              </label>
            </div>
            <div class="form-group">
              <label class="radio">
                <input type="radio" name="authProvider" value="twitter">
                <i class="icon-twitter"></i> Twitter
              </label>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Continue</button>
          </div>
          
        </form>
      </div>
      
    </section>
  </div>
  
  <?php include Tpl::file('seg/foot.php') ?>
  <?php include Tpl::file('seg/commonbottom.php') ?>
  
</body>
</html>