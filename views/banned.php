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
<?php include 'seg/commontop.php' ?>
<?php include 'seg/google.tracker.php' ?>
</head>
<body id="banned">
  
  <section id="ban-info">
  
    <h1 class="bantype-<?=$this->banType?>">
      <?php if(empty($this->ban)): ?>
      No active ban found
      <?php else: ?>
      You have been banned!
      <?php endif; ?>
      <?php if($this->banType != 'none'): ?>
      <br /><small><?=ucwords($this->banType)?> ban</small>
      <?php endif; ?>
    </h1>
    
    <?php if(!empty($this->ban)): ?>
    <dl>
      <dt>Banned user</dt>
      <dd><?=Tpl::out($this->user['username'])?></dd>
      <dt>Time of ban</dt>
      <dd><?=Tpl::moment(Date::getDateTime($this->ban['starttimestamp']), Date::STRING_FORMAT)?></dd>
      <?php if($this->ban['endtimestamp']): ?>
      <dt>Ending on</dt>
      <dd><?=Tpl::moment(Date::getDateTime($this->ban['endtimestamp']), Date::STRING_FORMAT)?></dd>
      <?php endif; ?>
      <dt>Ban reason</dt>
      <dd><?=Tpl::out($this->ban['reason'])?></dd>
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
  
  <?php include 'seg/commonbottom.php' ?>
</body>
</html>