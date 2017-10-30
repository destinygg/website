<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" charset="utf-8">
<meta name="referrer" content="no-referrer">
<?php include 'seg/meta.php' ?>
<link href="<?=Config::cdnv()?>/chat.css" rel="stylesheet" media="screen">
<style id="chat-styles" type="text/css"></style>
</head>
<body class="embed">

<div id="chat" class="chat chat-icons">

    <div id="chat-windows-select"></div>

    <div id="chat-output-frame" data-scroll-notify></div>

    <div id="chat-input-frame">
        <div id="chat-input-wrap">
            <textarea id="chat-input-control" placeholder="Getting things ready ..." autocomplete="off" tabindex="1" autofocus spellcheck></textarea>
        </div>
        <div id="chat-tools-wrap">
            <a id="chat-emoticon-btn" class="chat-tool-btn" title="Emotes">
                <span class="fa fa-smile-o"></span>
            </a>
            <a id="chat-whisper-btn" class="chat-tool-btn" title="Whispers">
                <span class="fa fa-comments-o"></span>
            </a>
            <div style="flex:1;"></div>
            <a id="chat-settings-btn" class="chat-tool-btn" title="Settings">
                <span class="fa fa-cog"></span>
            </a>
            <a id="chat-users-btn" class="chat-tool-btn" title="Users">
                <span class="fa fa-user"></span>
            </a>
        </div>
    </div>

    <div id="chat-settings" class="chat-menu right">
        <div class="list-wrap">
            <div class="toolbar">
                <h5><span>Settings</span> <i class="fa fa-chevron-circle-right menu-close"></i></h5>
            </div>
            <div class="scrollable nano">
                <div class="content nano-content">
                    <div id="chat-settings-form">

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
                            <label title="Ignore messages that mention ignored users">
                                <input name="ignorementions" type="checkbox" /> Harsh ignore
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="showremoved">Banned messages</label>
                            <select class="form-control" id="showremoved" name="showremoved">
                                <option value="0">remove</option>
                                <option value="1">&lt;censored&gt;</option>
                                <option value="2">do nothing</option>
                            </select>
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

                        <h4>Highlights, Focus &amp; Tags</h4>
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
                            <label title="Toggle the auto-complete suggestion tabs ...">
                                <input name="autocompletehelper" type="checkbox" /> Auto-complete helper
                            </label>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="chat-user-list" class="chat-menu right">
        <div class="list-wrap">
            <div class="toolbar">
                <h5><span>Users</span> <i class="fa fa-chevron-circle-right menu-close"></i></h5>
            </div>
            <div class="scrollable nano">
                <div class="content nano-content"></div>
            </div>
            <div id="chat-user-list-search">
                <input type="text" class="form-control" value="" placeholder="Username search ..." />
            </div>
        </div>
    </div>

    <div id="chat-emote-list" class="chat-menu left">
        <div class="list-wrap">
            <div class="toolbar">
                <h5><span>Emotes</span> <i class="fa fa-chevron-circle-left menu-close"></i></h5>
            </div>
            <div class="scrollable nano">
                <div class="content nano-content">
                    <div class="emote-group" id="destiny-emotes"></div>
                    <div id="emote-subscribe-note">Twitch subscribers only</div>
                    <div class="emote-group" id="twitch-emotes"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="chat-whisper-users" class="chat-menu left">
        <div class="list-wrap">
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

    <div id="chat-login-screen" style="display: none">
        <h2>Want to chat?</h2>
        <p>You need to be signed in.<br />Become part of the community and have your say!</p>
        <div>
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

<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/chat.js"></script>

</body>
</html>
