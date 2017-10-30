<?php
namespace Destiny;
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

        <form id="loginForm" action="/login" method="post">
          <input type="hidden" name="follow" value="<?=Tpl::out($this->follow)?>" />
          <div class="ds-block">

            <div class="form-group">
              <div class="controls checkbox">
                <label>
                  <input tabindex="1" autofocus type="checkbox" name="rememberme" <?=($this->rememberme) ? 'checked':''?>> Remember me
                </label>
                <span class="help-block">(this should only be used if you are on a private computer)</span>
              </div>
            </div>
            
            <div id="loginFormProviders">
              <?php if(in_array('twitch', Config::$a['authProfiles'])): ?>
              <div class="form-group">
                <label tabindex="2">
                  <input type="radio" name="authProvider" value="twitch" class="hidden">
                  <i class="icon-twitch"></i> Twitch
                </label>
              </div>
              <?php endif; ?>
              <?php if(in_array('reddit', Config::$a['authProfiles'])): ?>
              <div class="form-group">
                <label tabindex="3">
                  <input type="radio" name="authProvider" value="reddit" class="hidden">
                  <i class="icon-reddit"></i> Reddit
                </label>
              </div>
              <?php endif; ?>
              <?php if(in_array('google', Config::$a['authProfiles'])): ?>
              <div class="form-group">
                <label tabindex="4">
                  <input type="radio" name="authProvider" value="google" class="hidden">
                  <i class="icon-google"></i> Google
                </label>
              </div>
              <?php endif; ?>
              <?php if(in_array('twitch', Config::$a['authProfiles'])): ?>
              <div class="form-group">
                <label tabindex="5">
                  <input type="radio" name="authProvider" value="twitter" class="hidden">
                  <i class="icon-twitter"></i> Twitter
                </label>
              </div>
              <?php endif; ?>

            </div>
          </div>
          
        </form>
      </div>
      
    </section>
  </div>
  
  <?php include 'seg/foot.php' ?>
  <?php include 'seg/tracker.php' ?>
  <script src="<?=Config::cdnv()?>/web.js"></script>
  <script>
    $('#loginForm').each(function(){
      var form = $(this);
      form.on('click', '#loginFormProviders label', function(){
        $(this).find('[type="radio"]').prop('checked', true);
        form.trigger('submit');
        return false;
      });
      form.on('keyup', '#loginFormProviders label', function(e){
        if (e.keyCode === 13){
          $(this).find('[type="radio"]').prop('checked', true);
          form.trigger('submit');
          return false;
        }
      });
    });
  </script>

</body>
</html>