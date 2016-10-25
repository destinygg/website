<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta charset="utf-8">
<?php include 'seg/commontop.php' ?>
<link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
<style>
  .btn-post { min-width: 75px; }
</style>
</head>
<body id="authentication" class="no-contain">
  <div id="page-wrap">

    <?php include 'seg/top.php' ?>
    <?php include 'seg/alerts.php' ?>
    <?php include 'menu.php' ?>
    
    <section class="container">
      <h3 class="collapsed" data-toggle="collapse" data-target="#authentication-content">Providers</h3>
      <div id="authentication-content" class="content content-dark collapse clearfix">
        <div class="ds-block">
          <p>Authentication providers are what we use to know who you are! you can login with any of the services below.</p>
        </div>
        <form id="auth-profile-form" method="post">
          <table class="grid" style="width:100%">
            <thead>
              <tr>
                <td>Profile</td>
                <td style="width:100%;"></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach(Config::$a ['authProfiles'] as $profileType): ?>
              <tr>
                <td>
                   <?=ucwords($profileType)?>
                </td>
                <td>
                  <?php if(in_array($profileType, $this->authProfileTypes)): ?>
                    <?php $this->requireConnections = true; ?>
                    <span class="subtle"><span class="fa fa-check"></span> Connected</span>
                  <?php else: ?>
                    <a href="/profile/connect/<?=$profileType?>" class="btn btn-primary btn-xs btn-post">Connect</a>
                  <?php endif ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </form>
        
      </div>
    </section>
    
    <section class="container active">
      <h3 class="collapsed" data-toggle="collapse" data-target="#login-key-content">Login keys</h3>
      <div id="login-key-content" class="content content-dark clearfix collapse">
        <div class="ds-block">
          <p>Login keys allow you to authenticate with the destiny.gg chat without the need for a username or password.</p>
        </div>
        <form id="authtoken-form" action="/profile/authtoken/create" method="post">
          <table class="grid" style="width:100%">
            <thead>
              <tr>
                <td>Key</td>
                <td></td>
                <td style="width:100%;">Created</td>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($this->authTokens)): ?>
              <?php foreach($this->authTokens as $authToken): ?>
              <tr>
                <td><a href="/profile/authtoken/<?=$authToken['authToken']?>/delete" class="btn btn-danger btn-xs btn-post">Delete</a></td>
                <td><span><?=$authToken['authToken']?></span></td>
                <td><?=Date::getDateTime($authToken['createdDate'])->format(Date::STRING_FORMAT)?></td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
              <tr>
                <td colspan="3"><span class="subtle">You have no authentication keys</span></td>
              </tr>
              <?php endif ?>
            </tbody>
          </table>

          <div id="recaptcha" class="form-group ds-block hidden"> 
            <label>How Can Mirrors Be Real If Our Eyes Aren't Real?</label> 
            <div class="controls">
              <div class="g-recaptcha" data-sitekey="<?= Config::$a ['g-recaptcha'] ['key'] ?>"></div>
            </div>
          </div>
          
          <div class="form-actions">
            <button class="btn btn-primary" id="btn-create-key">Create new key</button>
          </div>

        </form>
      </div>
    </section>
  </div>
  
  <?php include 'seg/foot.php' ?>
  <?php include 'seg/commonbottom.php' ?>
  <script src="<?=Config::cdnv()?>/web.js"></script>

  <script src="//www.google.com/recaptcha/api.js"></script>
  <script>
  $('.btn-post').on('click', function(){
    var a = $(this), form = $(this).closest('form');
    form.attr("action", a.attr("href"));
    form.trigger('submit');
    return false;
  });
  $('#btn-create-key').on('click', function(){
    var recaptcha = $('#recaptcha'), form = $(this).closest('form');
    if(recaptcha.hasClass('hidden')){
      recaptcha.removeClass('hidden')
    }else{
      form.submit()
    }
    return false;
  });

  </script>

</body>
</html>