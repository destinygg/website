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
<body id="admin" class="no-contain">
  <div id="page-wrap">
    <?php include Tpl::file('seg/top.php') ?>
    <?php include Tpl::file('seg/admin.nav.php') ?>

    <section class="container">
      <?php if ( ! empty( $model->activeBans ) ): ?>
      <h3><?=Tpl::out( sprintf('Active bans (%d)', count( $model->activeBans ) ) )?></h3>
      <div class="content content-dark clearfix">
        <table class="grid">
          <thead>
            <tr>
              <td style="width: 200px;">User</td>
              <td>Reason</td>
              <td style="width: 80px;">Created on</td>
              <td style="width: 80px;">Ends on</td>
              <td><a onclick="return confirm('Are you sure?');" class="btn btn-danger btn-xs" href="/admin/bans/purgeall">Remove all bans!</a></td>
            </tr>
          </thead>
          <tbody>
          <?php foreach($model->activeBans as $ban): ?>
          <tr>
            <td><a href="/admin/user/<?=$ban['targetuserid']?>/edit"><?=Tpl::out($ban['targetusername'])?></a></td>
            <td class="wrap">Banned by <?=Tpl::out($ban['banningusername'])?> with reason: <?=Tpl::out($ban['reason'])?></td>
            <td><?=Tpl::moment(Date::getDateTime($ban['starttimestamp']), Date::STRING_FORMAT)?></td>
            <td>
              <?php if ( !$ban['endtimestamp'] )
                  echo "Permanent";
                else
                  echo Tpl::moment(Date::getDateTime($ban['endtimestamp']), Date::STRING_FORMAT);
              ?>
            </td>
            <td><a class="btn btn-danger btn-xs" href="/admin/user/<?=$ban['targetuserid']?>/ban/remove?follow=<?=rawurlencode( $_SERVER['REQUEST_URI'] )?>">Remove</a>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <h3>No active bans</h3>
      <div class="content content-dark clearfix">
        <div class="ds-block">
          <p>Good job for not being an asshole!</p>
        </div>
      </div>
      <?php endif; ?>
    </section>
  </div>
  
  <?php include Tpl::file('seg/foot.php') ?>
  <?php include Tpl::file('seg/commonbottom.php') ?>
  
  <script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
  
</body>
</html>