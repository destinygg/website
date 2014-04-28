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
</head>
<body id="authentication" class="profile">

  <?php include Tpl::file('seg/top.php') ?>
  <?php include Tpl::file('seg/headerband.php') ?>

  <section class="container">
    <ol class="breadcrumb" style="margin-bottom:0;">
      <li><a href="/profile" title="Your personal details">Profile</a></li>
      <li class="active" title="Your login methods">Authentication</li>
    </ol>
  </section>
  
  <section class="container collapsible active">
    <h3><span class="glyphicon glyphicon-chevron-down expander"></span> Providers</h3>

    <div class="content content-dark clearfix">
      <div class="ds-block">
        <p>Authentication providers are what we use to know who you are! you can login with any of the services below</p>
      </div>
      <table class="grid" style="width:100%">
        <thead>
          <tr>
            <td>Profile</td>
            <td style="width:100%;">Status</td>
          </tr>
        </thead>
        <tbody>
          <?php foreach(Config::$a ['authProfiles'] as $profileType): ?>
          <tr>
            <td>
              <i class="icon-<?=$profileType?>"></i> <?=ucwords($profileType)?>
            </td>
            <td>
              <?php if(in_array($profileType, $model->authProfileTypes)): ?>
              <?php $model->requireConnections = true; ?>
              <span class="subtle"><span class="glyphicon glyphicon-ok"></span> Connected</span>
              <?php else: ?>
              <a href="/profile/connect/<?=$profileType?>" class="btn btn-primary btn-xs">Connect</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
    </div>
  </section>
  
  <section class="container collapsible active">
    <h3><span class="glyphicon glyphicon-chevron-down expander"></span> Login keys</h3>
  
    <div class="content content-dark clearfix">
      <div class="ds-block">
        <p>Login keys allow you to authenticate with the destiny.gg chat without the need for a username or password. Keys MUST be kept a <strong>confidential</strong>.</p>
      </div>
      <form action="/profile/authtoken/create" method="post">
        <table class="grid" style="width:100%">
          <thead>
            <tr>
              <td>Key</td>
              <td style="width:100%;">Created</td>
            </tr>
          </thead>
          <tbody>
            <?php if(!empty($model->authTokens)): ?>
            <?php foreach($model->authTokens as $authToken): ?>
            <tr>
              <td><a href="/profile/authtoken/<?=$authToken['authToken']?>/delete" class="btn btn-danger btn-xs">Delete</a> <span><?=$authToken['authToken']?></span></td>
              <td><?=Date::getDateTime($authToken['createdDate'])->format(Date::STRING_FORMAT)?></td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="2"><span class="subtle">You have no authentication keys</span></td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
        
        <div class="form-actions">
          <button class="btn btn-primary btn-lg">Create new key</button>
        </div>

      </form>
    </div>
  </section>
  
  <?php include Tpl::file('seg/foot.php') ?>
  <?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>