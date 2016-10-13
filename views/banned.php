<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title>Ban Information</title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="banned">
  
  <section id="ban-info">
  
    <h1 class="bantype-<?=$model->banType?>">
      <?php if(empty($model->ban)): ?>
      No active ban found
      <?php else: ?>
      You have been banned!
      <?php endif; ?>
      <?php if($model->banType != 'none'): ?>
      <br /><small><?=ucwords($model->banType)?> ban</small>
      <?php endif; ?>
    </h1>
    
    <?php if(!empty($model->ban)): ?>
    <dl>
      <dt>Banned user</dt>
      <dd><?=Tpl::out($model->user['username'])?></dd>
      <dt>Time of ban</dt>
      <dd><?=Tpl::moment(Date::getDateTime($model->ban['starttimestamp']), Date::STRING_FORMAT)?></dd>
      <?php if($model->ban['endtimestamp']): ?>
      <dt>Ending on</dt>
      <dd><?=Tpl::moment(Date::getDateTime($model->ban['endtimestamp']), Date::STRING_FORMAT)?></dd>
      <?php endif; ?>
      <dt>Ban reason</dt>
      <dd><?=Tpl::out($model->ban['reason'])?></dd>
    </dl>
    
    <?php endif;?>
    
    <p>
      Any non-permanent bans are removed when subscribing as well
      as any mutes (there are no permanent mutes, maximum 6 days long).<br/>
      This is not meant to be a cash grab, rather a tool for those who would 
      not like to wait for a manual unban or for the ban to naturally expire 
      and are willing to pay for it.<br />
      Feel free to evade the ban if you have da skillz.
    </p>
    
    <br />
    
    <a href="/embed/chat" class="btn btn-primary">Try chat again?</a>
    
  </section>
  
  <?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>