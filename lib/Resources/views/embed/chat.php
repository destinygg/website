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
<meta name="referrer" content="no-referrer">
<?php include Tpl::file('seg/commontop.php') ?>
<link href="<?=Config::cdnv()?>/chat/css/style.min.css" rel="stylesheet" media="screen">
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body class="embed">

<div id="destinychat" class="chat chat-icons <?php if(Session::hasRole(UserRole::USER)): ?>authenticated<?php endif; ?>">
    <!-- chat output -->
    <div id="chat-output-frame">
        <div id="chat-output" class="nano">
          <div id="chat-lines" class="overthrow nano-content"></div>
          <div id="chat-scroll-notify">More messages below</div>
        </div>
    </div>
    <!-- end chat output -->
    
    <!-- chat input -->
    <form id="chat-input">
        <div class="clearfix">
            <div id="chat-input-wrap">
                <div id="chat-input-control">
                    <input type="text" placeholder="Enter a message..." class="input" spellcheck="true"/>
                    <span id="emoticon-btn" class="fa fa-smile-o" title="Emotes"></span>
                </div>
                <span id="chat-login-msg">
                  <?php if(!empty($model->follow)): ?>
                      You must <a href="/login?follow=<?= Tpl::out($model->follow) ?>" target="_parent">sign in</a> to chat
                  <?php else: ?>
                      You must <a href="/login" target="_parent">sign in</a> to chat
                  <?php endif; ?>
                </span>
            </div>
            <div id="chat-tools-wrap">
                <a id="chat-settings-btn" class="iconbtn" title="Settings">
                    <span class="fa fa-cog"></span>
                </a>
                <a id="chat-users-btn" class="iconbtn" title="Users">
                    <span id="chat-pm-count" class="hidden flash" title="You have unread messages!">0</span>
                    <span class="fa fa-user"></span>
                </a>
            </div>
        </div>
    </form>
    <!-- end chat input -->
    
    <!-- top frame -->
    <div id="chat-top-frame">
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
              
              <div class="tools">
                <div class="form-group checkbox">
                  <label class="checkbox" title="Show all user flair icons">
                    <input name="hideflairicons" type="checkbox" /> Hide flair icons
                  </label>
                </div>
                <div class="form-group checkbox">
                  <label class="checkbox" title="Show the timestamps next to the messages">
                    <input name="showtime" type="checkbox" /> Show time for messages
                  </label>
                </div>
                <div class="form-group checkbox">
                  <label class="checkbox" title="Highlight text that you are mentioned in">
                    <input name="highlight" type="checkbox" checked="checked"/> Highlight on mention
                  </label>
                </div>
                <div class="form-group checkbox">
                  <label class="text" title="Your custom list of words that will make messages highlight" style="width: 100%;">
                    Custom highlight words.
                    <input name="customhighlight" type="text" class="form-control input-sm" placeholder="Separated using a comma (,)" />
                  </label>
                </div>
                <div class="form-group checkbox">
                  <label class="checkbox" title="Show desktop notifications on hightlight">
                    <input name="allowNotifications" type="checkbox" /> Desktop notification on highlight
                  </label>
                </div>
                <div class="form-group checkbox">
                  <hr style="margin:5px 0;">
                  See the <a href="/chat/faq" target="_blank">chat FAQ</a> for more information
                </div>
              </div>
              
            </div>
        </div>
        <!-- end settings -->
    
        <!-- emote list -->
        <div id="chat-emote-list" class="chat-menu">
            <div class="list-wrap clearfix">
            
              <div class="toolbar">
                <h5>
                  Emotes
                  <button type="button" class="close" aria-hidden="true">&times;</button>
                </h5>
              </div>
            
              <div id="chat-emotes" class="scrollable nano">
                <div class="content nano-content">
                  <div class="emote-group" id="destiny-emotes"></div>
                  <hr/>
                  <h6>Twitch Emotes</h6>
                  <div id="emote-subscribe-note">Subscribe on Twitch to unlock Emotes</div>
                  <div class="emote-group" id="twitch-emotes"></div>
                  <hr/>
                </div>
              </div>
            
            </div>
        </div>
        <!-- emote list -->

        <div id="chat-private-messages" class="chat-menu">
            <div class="list-wrap clearfix">
              <div class="toolbar">
                <h5>
                  Messages
                  <button type="button" class="close" aria-hidden="true">&times;</button>
                </h5>
              </div>
              <div id="chat-pm-message">
                <p>You have unread message(s).</p>
                <p>
                    <a id="reply-privmsg" href="/profile/messages" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-envelope"></i> INBOX</a> or
                    <button id="close-privmsg" class="btn btn-default btn-sm">DISMISS <i class="fa fa-close"></i></button>
                </p>
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
