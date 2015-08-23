<?php
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin" class="thin">

  <?php include Tpl::file('seg/top.php') ?>
  
  <section class="container">
    <ol class="breadcrumb" style="margin-bottom:0;">
      <li><a href="/admin">Users</a></li>
      <li><a href="/admin/chat">Chat</a></li>
      <li class="active">Subscribers</li>
      <li><a href="/admin/bans">Bans</a></li>
    </ol>
  </section>
  
  <?php function buildSubscribersTier(array $tier = null, $num){?>
  <?php if(!empty($tier)): ?>
  <section class="container">
    <h3>T<?=$num?> Subscribers</h3>
    <div class="content content-dark clearfix">
      <table class="grid">
        <thead>
          <tr>
            <td style="width: 20px;"></td>
            <td style="width: 200px;">User</td>
            <td style="width: 100px;">Recurring</td>
            <td style="width: 80px;">Created on</td>
            <td>Ends on</td>
          </tr>
        </thead>
        <tbody>
        <?php $i=1; ?>
        <?php foreach($tier as $sub): ?>
        <tr>
          <td><?=$i?></td>
          <td><a href="/admin/user/<?=$sub['userId']?>/edit"><?=Tpl::out($sub['username'])?></a></td>
          <td><?=($sub['recurring'] == 1) ? 'Yes':'No'?></td>
          <td><?=Tpl::moment(Date::getDateTime($sub['createdDate']), Date::STRING_FORMAT)?></td>
          <td><?=Tpl::moment(Date::getDateTime($sub['endDate']), Date::STRING_FORMAT)?></td>
        </tr>
        <?php $i++; endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
  <?php endif; ?>
  <?php } ?>

  <?php if(empty($model->subscribersT4) && empty($model->subscribersT3) && empty($model->subscribersT2) && empty($model->subscribersT1)): ?>
  <section class="container">
    <h3>Subscribers</h3>
    <div class="content content-dark clearfix">
      <div class="ds-block">
        <p>No subscribers</p>
      </div>
    </div>
  </section>
  <?php endif; ?>
  
  <?php buildSubscribersTier($model->subscribersT4, 4) ?>
  <?php buildSubscribersTier($model->subscribersT3, 3) ?>
  <?php buildSubscribersTier($model->subscribersT2, 2) ?>
  <?php buildSubscribersTier($model->subscribersT1, 1) ?>
  
  <br />
  <?php include Tpl::file('seg/commonbottom.php') ?>
  
  <script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
  
</body>
</html>