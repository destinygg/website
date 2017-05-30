<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\User\UserRole;
use Destiny\Common\Session;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" charset="utf-8">
<meta name="referrer" content="no-referrer">
<?php include 'seg/commontop.php' ?>
<link href="<?=Config::cdnv()?>/chat.css" rel="stylesheet" media="screen">
<style id="chat-styles" type="text/css"></style>
</head>
<body class="embed">

<div id="chat" class="chat chat-icons">

    <!-- chat output -->
    <div id="chat-output-frame">
        <div id="chat-output" class="nano">
            <div id="chat-lines" class="nano-content"></div>
            <div id="chat-scroll-notify">More messages below</div>
        </div>
    </div>
    <!-- end chat output -->

    <!-- chat input -->
    <div id="chat-input">
        <div id="chat-input-wrap">
            <textarea id="chat-input-control" placeholder="Write something..." spellcheck="true" autocomplete="off" tabindex="1" autofocus disabled></textarea>
        </div>
        <div id="chat-tools-wrap">
            <a id="chat-emoticon-btn" class="iconbtn" title="Emotes" style="float: left;">
                <span class="fa fa-smile-o"></span>
            </a>
            <a id="chat-whisper-btn" class="iconbtn" title="Whispers" style="float: left;">
                <span class="fa fa-comments-o"></span>
            </a>
            <a id="chat-settings-btn" class="iconbtn" title="Settings">
                <span class="fa fa-cog"></span>
            </a>
            <a id="chat-users-btn" class="iconbtn" title="Users">
                <span class="fa fa-user"></span>
            </a>
        </div>
    </div>
    <!-- end chat input -->

    <!-- bot frame -->
    <div id="chat-bottom-frame">

        <!-- user list -->
        <div id="chat-user-list" class="chat-menu">
            <div class="list-wrap clearfix">
              <div class="toolbar">
                <h5>Users (~<span>0</span>) <button type="button" class="close" aria-hidden="true">&times;</button></h5>
              </div>
              <div class="scrollable nano">
                <div id="chat-groups" class="content nano-content"></div>
              </div>
            </div>
        </div>
        <!-- end user list -->

        <!-- settings -->
        <div id="chat-settings" class="chat-menu">
            <div class="list-wrap clearfix">
                <div class="toolbar">
                    <h5>Settings <button type="button" class="close" aria-hidden="true">&times;</button></h5>
                </div>
                <div class="tools">
                    <div class="checkbox">
                        <label title="Persistent profile settings">
                            <input name="profilesettings" type="checkbox" checked="checked"/> Save settings to profile
                        </label>
                    </div>
                    <div class="checkbox">
                        <label title="Show all user flair icons">
                            <input name="hideflairicons" type="checkbox" /> Hide flair icons
                        </label>
                    </div>
                    <div class="checkbox">
                        <label title="Show the timestamps next to the messages">
                            <input name="showtime" type="checkbox" /> Show time for messages
                        </label>
                    </div>
                    <div class="checkbox">
                        <label title="Show removed content">
                            <input name="showremoved" type="checkbox" /> Show removed messages
                        </label>
                    </div>
                    <div class="checkbox">
                        <label title="Highlight text that you are mentioned in">
                            <input name="highlight" type="checkbox" checked="checked"/> Highlight on mention
                        </label>
                    </div>
                    <div class="form-group">
                        <label title="Your custom list of words that will make messages highlight" style="width: 100%;">
                            Custom highlight words.
                            <input name="customhighlight" type="text" class="form-control input-sm" placeholder="Separated using a comma (,)" />
                        </label>
                    </div>
                    <div class="checkbox">
                        <label title="Show desktop notifications on highlight">
                            <input name="allowNotifications" type="checkbox" /> Desktop notifications
                            <br /><small id="chat-settings-notification-permissions">(Permission unknown)</small>
                        </label>
                    </div>
                    <div class="checkbox">
                        <label title="Show whispers in chat">
                            <input name="showhispersinchat" type="checkbox" /> Show whispers in chat
                        </label>
                    </div>
                    <div class="form-group checkbox">
                        <hr />
                        <small>See the <a href="/chat/faq" target="_blank">chat FAQ</a> for more information</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- end settings -->

        <div id="chat-emote-list" class="chat-menu">
            <div class="list-wrap clearfix">
              <div class="toolbar">
                <h5><span>Emotes</span> <button type="button" class="close" aria-hidden="true">&times;</button></h5>
              </div>
              <div id="chat-emotes" class="scrollable nano">
                <div class="content nano-content">
                  <div class="emote-group" id="destiny-emotes"></div>
                  <hr/>
                  <h6>Twitch Emotes</h6>
                  <div id="emote-subscribe-note">Twitch subscribers only</div>
                  <div class="emote-group" id="twitch-emotes"></div>
                </div>
              </div>
            </div>
        </div>

        <div id="chat-whisper-users" class="chat-menu">
            <div class="list-wrap clearfix">
                <div class="toolbar">
                    <h5><span>Whispers</span> <button type="button" class="close" aria-hidden="true">&times;</button></h5>
                </div>
                <div class="scrollable nano">
                    <div class="content nano-content">
                        <ul></ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="chat-whisper-messages" class="chat-menu">
            <div class="list-wrap clearfix">
                <div class="toolbar">
                    <h5><span>Whisper</span> <button type="button" class="close" aria-hidden="true">&times;</button></h5>
                </div>
                <div class="scrollable nano">
                    <div class="content nano-content"></div>
                </div>
            </div>
        </div>

    </div>
    <!-- end bot frame -->

    <div id="chat-login-screen" style="display:none;">
        <div style="text-align: center;">
            <div>
                <h2>Want to chat?</h2>
                <p>You need to be signed in.<br />Become part of the community and have your say!</p>
            </div>
            <button id="chat-btn-login" class="btn btn-primary">Login</button>
            <button id="chat-btn-cancel" class="btn btn-default">No thanks</button>
        </div>
    </div>

    <div id="chat-loading">
        <div>
            <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
            <span class="sr-only">Loading...</span>
        </div>
    </div>

</div>

<?php include 'seg/commonbottom.php' ?>
<script src="<?=Config::cdnv()?>/chat.js"></script>

</body>
</html>
