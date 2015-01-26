<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserFeature;
use Destiny\Common\Session;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" charset="utf-8">
<meta name="referrer" content="none">
<?php include Tpl::file('seg/commontop.php') ?>
<link href="<?=Config::cdnv()?>/chat/css/style.min.css" rel="stylesheet" media="screen">
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body class="embed">

<div id="destinychat" class="chat chat-theme-dark chat-icons">
    
    <!-- chat output -->
    <div class="chat-output-frame">
        <div class="chat-output nano">
          <div class="chat-lines overthrow nano-content"></div>
        </div>
    </div>
    <!-- end chat output -->
    
    <!-- chat input -->
    <?php if(Session::hasRole(UserRole::USER)): ?>
    <form class="chat-input">
        <div class="clearfix">
          <div class="chat-input-wrap">
            <div class="chat-input-control">
              <input type="text" placeholder="Enter a message..." class="input" autocomplete="off" spellcheck="true"/>
            </div>
          </div>
          <div class="chat-tools-wrap">
            <a class="iconbtn chat-settings-btn" title="Settings">
              <span class="fa fa-cog"></span>
            </a>
            <a class="iconbtn chat-users-btn" title="Users">
              <span class="chat-pm-count hidden flash" title="You have unread messages!">0</span>
              <span class="fa fa-user"></span>
            </a>
          </div>
        </div>
    </form>
    <?php else: ?>
    <form class="chat-input">
        <div class="clearfix">
          <div class="chat-input-wrap">
            <span class="chat-login-msg">
              <?php if(!empty($model->follow)): ?>
              You must <a href="/login?follow=<?= Tpl::out($model->follow) ?>" target="_parent">sign in</a> to chat
              <?php else: ?>
              You must <a href="/login" target="_parent">sign in</a> to chat
              <?php endif; ?>
            </span>
            <input type="hidden" class="input" />

          </div>
          <div class="chat-tools-wrap">
            <a class="iconbtn chat-users-btn" title="Users">
              <span class="fa fa-user"></span>
            </a>
          </div>
        </div>
    </form>
    <?php endif; ?>
    <!-- end chat input -->
    
    <!-- top frame -->
    <div id="chat-top-frame">
      
        <!-- hints -->
        <div class="hint-popup">
            <div class="wrap clearfix">
              <div class="alert alert-warning">
                <a class="hidehint" title="Hide hint"><span class="fa fa-remove"></span></a>
                <a class="nexthint" title="Next hint"><span class="fa fa-chevron-right"></span></a>
                <strong>Hint:</strong> <span class="hint-message"></span>
              </div>
            </div>
        </div>
        <!-- end hints -->

        <!-- user tools -->
        <div class="user-tools">
            <div class="wrap clearfix">
              <h5>
                <button type="button" class="close" aria-hidden="true">&times;</button>
                <span class="user-tools-user"></span>
              </h5>
              <div class="tools">
              
                <div class="user-tools-wrap">
                
                  <a id="ignoreuser" href="#ignore">
                    <span class="fa fa-eye-slash"></span> Ignore
                  </a>
                  <a id="unignoreuser" href="#unignore">
                    <span class="fa fa-eye"></span> Unignore
                  </a>
            
                  <?php if(Session::hasFeature(UserFeature::MODERATOR) || Session::hasFeature(UserFeature::ADMIN)): ?>
                  <a href="#togglemute">
                    <span class="fa fa-ban"></span> Mute
                  </a> 
                  <a href="#toggleban">
                    <span class="fa fa-remove"></span> Ban
                  </a> 
                  <a href="#clearmessages"><span class="fa fa-fire"></span> Clear messages</a> 
                  <?php endif; ?>
            
                </div>
            
                <?php if(Session::hasFeature(UserFeature::MODERATOR) || Session::hasFeature(UserFeature::ADMIN)): ?>
                <!-- mute -->
                <form id="user-mute-form">
                  <div class="form-group">
                    <select id="banTimeLength" class="select form-control input-sm">
                      <option value="0">Length of time</option>
                      <option value="10">10 minutes</option>
                      <option value="30">30 minutes</option>
                      <option value="60">1 hr</option>
                      <option value="720">12 hrs</option>
                      <option value="1440">24 hrs</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <button type="submit" class="btn btn-xs btn-primary">Confirm</button>
                    <button id="cancelmute" type="button" class="btn btn-xs">Cancel</button>
                  </div>
                </form>
                <!-- end mute -->
                
                <!-- ban -->
                <form id="user-ban-form">
                  <input type="hidden" name="ipBan" value="" />
                  <div class="form-group">
                    <select id="banTimeLength" class="select form-control input-sm" style="width:150px;" onchange="$('#banReason').focus();">
                      <option value="0">Length of time</option>
                      <option value="1">1 minute</option>
                      <option value="5">5 minutes</option>
                      <option value="10">10 minutes</option>
                      <option value="30">30 minutes</option>
                      <option value="60">1 hr</option>
                      <option value="720">12 hrs</option>
                      <option value="1440">24 hrs</option>
                      <option value="perm">Permanent</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <input type="text" class="input form-control input-sm" id="banReason" placeholder="Reason for ban" />
                  </div>
                  <div class="form-group">
                    <button type="submit" class="btn btn-xs btn-primary">Ban user</button>
                    <button id="ipbanuser" type="button" class="btn btn-xs btn-danger">IP ban user</button>
                    <button id="cancelban" type="button" class="btn btn-xs">Cancel</button>
                  </div>
                </form>
                <!-- end ban -->
                
                <?php endif; ?>
                
              </div>
            </div>
        </div>
        <!-- end user tools -->
        
        <!-- broadcast -->
        <div id="chat-broadcasts">
            <!-- template -->
            <div class="chat-broadcast alert alert-info hidden template">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <p>
                    <span class="fa fa-exclamation-triangle"></span>
                    <span class="message">This is a broadcast!</span>
                </p>
            </div>
            <!-- template -->
        </div>
        <!-- end broadcast -->
        
    </div>
    <!-- end top frame -->
    
    <!-- bot frame -->
    <div id="chat-bottom-frame">
    
        <!-- user list -->
        <div id="chat-user-list" class="chat-menu">
            <div class="list-wrap clearfix">
            
              <div class="toolbar">
                <h5>
                  Users (~<span></span>)
                  <button type="button" class="close" aria-hidden="true">&times;</button>
                </h5>
              </div>
            
              <div id="chat-groups" class="scrollable nano">
                <div class="content nano-content">
                  <h6>Admins</h6>
                  <ul class="unstyled admins"></ul>
                  <hr/>
                  <h6>VIP</h6>
                  <ul class="unstyled vips"></ul>
                  <hr/>
                  <h6>Moderators</h6>
                  <ul class="unstyled moderators"></ul>
                  <hr/>
                  <h6>Subscribers</h6>
                  <ul class="unstyled subs"></ul>
                  <hr/>
                  <h6>Plebs</h6>
                  <ul class="unstyled plebs"></ul>
                  <hr/>
                  <h6>Bots</h6>
                  <ul class="unstyled bots"></ul>
                </div>
              </div>
            
            </div>
        </div>
        <!-- end user list -->
  
        <!-- settings -->
        <div id="chat-settings" class="chat-menu">
            <div class="list-wrap clearfix">
            
              <div class="toolbar">
                <h5>
                  <span>Settings</span>
                  <button type="button" class="close" aria-hidden="true">&times;</button>
                </h5>
              </div>
              
              <ul class="unstyled">
                <li>
                  <label class="checkbox" title="Show all user flair icons">
                    <input name="hideflairicons" type="checkbox" /> Hide flair icons
                  </label>
                </li>
                <li>
                  <label class="checkbox" title="Show the timestamps next to the messages">
                    <input name="showtime" type="checkbox" /> Show time for messages
                  </label>
                </li>
                <li>
                  <label class="checkbox" title="Highlight text that you are mentioned in">
                    <input name="highlight" type="checkbox" checked="checked"/> Highlight on mention
                  </label>
                </li>
                <li>
                  <label class="text" title="Your custom list of words that will make messages highlight" style="width: 100%;">
                    Custom highlight words.
                    <input name="customhighlight" type="text" class="form-control input-sm" placeholder="Separated using a comma (,)" />
                  </label>
                </li>
                <li>
                  <label class="checkbox" title="Show desktop notifications on hightlight">
                    <input name="notifications" type="checkbox" /> Desktop notification on highlight
                  </label>
                </li>
                <li>
                  <hr style="margin:5px 0;">
                  See the <a href="/chat/faq" target="_blank">chat FAQ</a> for more information
                </li>
              </ul>
              
            </div>
        </div>
        <!-- end settings -->

        <div id="chat-private-messages" class="chat-menu">
            <div class="list-wrap clearfix">
              <div class="toolbar">
                <h5>
                  Messages
                  <button type="button" class="close" aria-hidden="true">&times;</button>
                </h5>
              </div>
              <div id="chat-pm-message">
                <p>You have <span class="chat-pm-count">0</span> unread message(s).</p>
                <p><a href="/profile/messages" target="_blank" class="btn btn-primary btn-sm reply-link"><i class="fa fa-envelope"></i> INBOX</a> or <button class="btn btn-default btn-sm close-link">DISMISS <i class="fa fa-close"></i></button></p>
                <p>or view the <a href="#" class="user-list-link">user list</a></p>
              </div>
            </div>
        </div>
    
    </div>
    <!-- end bot frame -->
    
  
</div>

<?php include Tpl::file('seg/commonbottom.php') ?>

<script src="/chat/history"></script>
<script src="<?=Config::cdnv()?>/chat/js/chat.min.js"></script>
<script>$('#destinychat').ChatGui(<?=Tpl::jsout($model->user)?>,<?=Tpl::jsout($model->options)?>);</script>

</body>
</html>
