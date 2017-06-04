<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Country;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta charset="utf-8">
<?php include 'seg/commontop.php' ?>
<link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="register" class="no-brand">
  <div id="page-wrap">

    <?php include 'seg/top.php' ?>
    <?php include 'seg/headerband.php' ?>
    
    <section class="container">
    
      <h1 class="title">
        <span>Confirm</span> 
        <small>your <i class="icon-<?=Tpl::out($this->authProvider)?>"></i> <?=Tpl::out(strtolower($this->authProvider))?> details</small>
      </h1>
      
      <?php if(!empty($this->error)): ?>
      <div class="alert alert-danger">
        <strong>Error!</strong>
        <?=Tpl::out($this->error->getMessage())?>
      </div>
      <?php endif ?>
      
      <div class="content content-dark clearfix">

        <div class="ds-block">
          <p>Almost there... since not all authentication providers support nick names, you get to choose your own.
          <br>Your email address is never shown publicly and no emails will be sent to you without your permission.
          </p>
        </div>

        <form action="/register" method="post">
          <input type="hidden" name="code" value="<?=Tpl::out($this->code)?>" />
          <input type="hidden" name="follow" value="<?=Tpl::out($this->follow)?>" />

          <div class="ds-block">
            <div class="form-group">
              <label class="control-label" for="inputUsername">Username / Nickname</label>
              <div class="controls">
                <input type="text" class="form-control" name="username" id="inputUsername" value="<?=Tpl::out($this->username)?>" placeholder="Username">
                <span class="help-block">A-z 0-9 and underscores. Must contain at least 3 and at most 20 characters</span>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label" for="inputEmail">Email</label>
              <div class="controls">
                <input type="text" class="form-control" name="email" id="inputEmail" value="<?=Tpl::out($this->email)?>" placeholder="Email">
                <span class="help-block">Be it valid or not, it will be safe with us.</span>
              </div>
            </div>
            <div class="form-group">
              <label>Country:</label> 
              <select name="country" class="form-control">
                <option value="">Select your country</option>
                <?$countries = Country::getCountries();?>
                <option value="">&nbsp;</option>
                <option value="US" <?php if($this->user['country'] == 'US'): ?>
                  selected="selected" <?php endif;?>>United States</option>
                <option value="GB" <?php if($this->user['country'] == 'GB'): ?>
                  selected="selected" <?php endif ?>>United Kingdom</option>
                <option value="">&nbsp;</option>
                <?php foreach($countries as $country): ?>
                <option value="<?=$country['alpha-2']?>" <?php if($this->user['country'] != 'US' && $this->user['country'] != 'GB' && $this->user['country'] == $country['alpha-2']):?>selected="selected" <?php endif;?>><?=Tpl::out($country['name'])?></option>
                <?php endforeach; ?>
              </select>
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
              <label>How Can Mirrors Be Real If Our Eyes Aren't Real?</label> 
              <div class="controls">
                <div class="g-recaptcha" data-sitekey="<?= Config::$a ['g-recaptcha'] ['key'] ?>"></div>
              </div>
            </div>

          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Continue</button>
            <a href="/login" class="btn btn-lg">Cancel</a>
            <p class="agreement">
              <span>By clicking the &quot;Continue&quot; button, you are confirming that you have read and agree with the <a href="/agreement">user agreement</a>.</span>
            </p>
          </div>
        </form>

      </div>
      
    </section>
  </div>
  
  <?php include 'seg/foot.php' ?>
  <?php include 'seg/commonbottom.php' ?>
  <script src="<?=Config::cdnv()?>/web.js"></script>
  <script src="https://www.google.com/recaptcha/api.js"></script>
  
</body>
</html>