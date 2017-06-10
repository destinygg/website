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

    <div id="chat-output-frame" data-scroll-notify></div>

    <div id="chat-input">
        <div id="chat-input-wrap">
            <textarea id="chat-input-control" placeholder="Write something..." spellcheck="true" autocomplete="off" tabindex="1" autofocus></textarea>
        </div>
        <div id="chat-tools-wrap">
            <div id="chat-windows-thumbnails"></div>
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

    <a id="chat-popout-btn" class="iconbtn" title="Popout" style="display: none;">
        <span class="fa fa-external-link-square"></span>
    </a>

    <div id="chat-settings" class="chat-menu right">
        <div class="list-wrap clearfix">
            <div class="toolbar">
                <h5>Settings <i class="fa fa-chevron-circle-right menu-close"></i></h5>
            </div>
            <div class="scrollable nano">
                <div class="content nano-content">
                    <div class="clearfix">

                        <div class="form-group checkbox">
                            <label title="Persistent profile settings">
                                <input name="profilesettings" type="checkbox"/> Save settings to profile
                            </label>
                        </div>

                        <h4>Messages</h4>
                        <div class="form-group checkbox">
                            <label title="Show all user flair icons">
                                <input name="hideflairicons" type="checkbox" data-opposite/> Show flairs
                            </label>
                        </div>
                        <div class="form-group checkbox">
                            <label title="Show the timestamps next to the messages">
                                <input name="showtime" type="checkbox" /> Show time
                            </label>
                        </div>
                        <div class="form-group checkbox">
                            <label title="&lt;censored&gt; instead of removing messages">
                                <input name="showremoved" type="checkbox" /> &lt;censored&gt;
                            </label>
                        </div>
                        <div class="form-group checkbox">
                            <label title="Ignore messages that mention ignored users">
                                <input name="ignorementions" type="checkbox" /> Harsh ignore
                            </label>
                        </div>

                        <h4>Notifications</h4>
                        <div class="form-group checkbox">
                            <label title="Notification auto close">
                                <input name="notificationtimeout" type="checkbox" /> Close after short period
                            </label>
                        </div>
                        <div class="form-group">
                            <p><small id="chat-settings-notification-permissions">(Permission unknown)</small></p>
                        </div>

                        <h4>Whispers</h4>
                        <div class="form-group checkbox">
                            <label title="Notification on whisper">
                                <input name="notificationwhisper" type="checkbox" /> Notification
                            </label>
                        </div>
                        <div class="form-group checkbox">
                            <label title="Whispers shown in chat">
                                <input name="showhispersinchat" type="checkbox" /> In-line messages
                            </label>
                        </div>

                        <h4>Highlights &amp; Focus &amp; Tags</h4>
                        <div class="form-group checkbox">
                            <label title="Notification on highlight">
                                <input name="notificationhighlight" type="checkbox" /> Notification
                            </label>
                        </div>
                        <div class="form-group checkbox">
                            <label title="Highlights messages you are mentioned in">
                                <input name="highlight" type="checkbox"/> Highlight when mentioned
                            </label>
                        </div>
                        <div class="form-group checkbox">
                            <label title="Include mentions when focused">
                                <input name="focusmentioned" type="checkbox" /> Include mentions when <span title="Occurs when you click on a user in chat.">focused</span>
                            </label>
                        </div>
                        <div class="form-group checkbox">
                            <label>
                                <input name="taggedvisibility" type="checkbox" /> Increased visibility of tagged users
                            </label>
                        </div>
                        <div class="form-group row">
                            <label title="Highlights usernames or text that match">Custom Highlights</label>
                            <textarea name="customhighlight" style="resize: vertical;" class="form-control" placeholder="Comma separated ..."></textarea>
                        </div>

                        <h4>Autocomplete</h4>
                        <div class="form-group checkbox">
                            <label title="Show or hid the auto complete helper">
                                <input name="autocompletehelper" type="checkbox" /> Auto-complete helper
                            </label>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="chat-user-list" class="chat-menu right">
        <div class="list-wrap clearfix">
            <div class="toolbar">
                <h5><span>Users</span> <i class="fa fa-chevron-circle-right menu-close"></i></h5>
            </div>
            <div class="scrollable nano">
                <div class="content nano-content"></div>
            </div>
            <div id="chat-user-list-search">
                <input type="text" class="form-control input-sm" value="" placeholder="Username search ..." />
            </div>
        </div>
    </div>

    <div id="chat-emote-list" class="chat-menu left">
        <div class="list-wrap clearfix">
            <div class="toolbar">
                <h5><span>Emotes</span> <i class="fa fa-chevron-circle-left menu-close"></i></h5>
            </div>
            <div class="scrollable nano">
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

    <div id="chat-whisper-users" class="chat-menu left">
        <div class="list-wrap clearfix">
            <div class="toolbar">
                <h5><span>Whispers</span> <i class="fa fa-chevron-circle-left menu-close"></i></h5>
            </div>
            <div class="scrollable nano">
                <div class="content nano-content">
                    <ul></ul>
                </div>
            </div>
        </div>
    </div>

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
