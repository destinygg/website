<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
<script src="//www.google.com/recaptcha/api.js"></script>
<style>
  .btn-post { min-width: 75px; }
</style>
</head>
<body id="authentication" class="profile">
  <div id="page-wrap">

    <?php include Tpl::file('seg/top.php') ?>
    <?php include Tpl::file('seg/headerband.php') ?>
    <?php include Tpl::file('seg/alerts.php') ?>
    <?php include Tpl::file('profile/menu.php') ?>
    
    <section class="container collapsible active">
      <h3><span class="fa fa-fw fa-chevron-down expander"></span> Providers</h3>

      <div class="content content-dark clearfix">
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
                  <?php if(in_array($profileType, $model->authProfileTypes)): ?>
                  <?php $model->requireConnections = true; ?>
                  <button class="btn btn-default btn-xs btn-post" disabled="disabled">Connected</button>
                  <?php else: ?>
                  <a href="/profile/connect/<?=$profileType?>" class="btn btn-primary btn-xs btn-post">Connect</a>
                  <?php endif; ?>
                </td>
                <td>
                   <?=ucwords($profileType)?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </form>
        
      </div>
    </section>
    
    <section class="container collapsible active">
      <h3><span class="fa fa-fw fa-chevron-down expander"></span> Login keys</h3>
    
      <div class="content content-dark clearfix">
        <div class="ds-block">
          <p>Login keys allow you to authenticate with the destiny.gg chat without the need for a username or password.</p>
        </div>
        <form id="authtoken-form"action="/profile/authtoken/create" method="post">
          <table class="grid" style="width:100%">
            <thead>
              <tr>
                <td>Key</td>
                <td></td>
                <td style="width:100%;">Created</td>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($model->authTokens)): ?>
              <?php foreach($model->authTokens as $authToken): ?>
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
              <?php endif; ?>
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
  
  <?php include Tpl::file('seg/foot.php') ?>
  <?php include Tpl::file('seg/commonbottom.php') ?>

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