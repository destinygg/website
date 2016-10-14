<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta charset="utf-8">
<?php include 'seg/commontop.php' ?>
<?php include 'seg/google.tracker.php' ?>
  <style>
    #loginFormProviders label {
      cursor: pointer;
      margin: 0;
    }
    #loginFormProviders label,
    #loginFormProviders i {
      vertical-align: middle;
    }
    #loginFormProviders i {
      margin-right: 3px;
    }
    #loginFormProviders label:hover {
      color: white;
    }
  </style>
</head>
<body id="login" class="no-brand">
  <div id="page-wrap">

    <?php include 'seg/top.php' ?>
    <?php include 'seg/headerband.php' ?>
    
    <section class="container">
    
      <h1 class="title">
        <span>Sign In</span>
        <small>...</small>
      </h1>
      
      <?php if(!empty($this->error)): ?>
      <div class="alert alert-danger">
        <strong>Error!</strong>
        <?=Tpl::out($this->error->getMessage())?>
      </div>
      <?php endif; ?>
      
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
            
            <div class="form-group">
              <h3>... With</h3>
            </div>

            <div id="loginFormProviders">
              <div class="form-group">
                <label tabindex="2">
                  <input type="radio" name="authProvider" value="twitch" class="hidden">
                  <i class="icon-twitch"></i> Twitch
                </label>
              </div>
              <div class="form-group">
                <label tabindex="3">
                  <input type="radio" name="authProvider" value="reddit" class="hidden">
                  <i class="icon-reddit"></i> Reddit
                </label>
              </div>
              <div class="form-group">
                <label tabindex="4">
                  <input type="radio" name="authProvider" value="google" class="hidden">
                  <i class="icon-google"></i> Google
                </label>
              </div>
              <div class="form-group">
                <label tabindex="5">
                  <input type="radio" name="authProvider" value="twitter" class="hidden">
                  <i class="icon-twitter"></i> Twitter
                </label>
              </div>
            </div>
          </div>
          
        </form>
      </div>
      
    </section>
  </div>
  
  <?php include 'seg/foot.php' ?>
  <?php include 'seg/commonbottom.php' ?>

</body>
</html>