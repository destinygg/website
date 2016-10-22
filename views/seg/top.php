<?php
namespace Destiny; 
use Destiny\Common\User\UserRole;
use Destiny\Common\Session;
?>
<div id="main-nav" class="navbar navbar-static-top navbar-inverse">
    <div class="container-fluid">
    
        <a class="brand pull-left visible-lg" href="/">Destiny.gg</a>

        <ul class="nav navbar-nav">
            <li id="menubtn">
                <a title="Menu" href="#" class="collapsed" aria-label="Menu" data-toggle="collapse" data-target="#collapsemenu">
                    <span class="menuicon"></span>
                </a>
            </li>
        </ul>

        <ul class="nav navbar-nav navbar-right pull-right">

            <li class="bigscreen">
                <a title="So. Much. Girth." href="/bigscreen" rel="bigscreen">
                    <i class="icon-bigscreen"></i>
                    <span class="visible-lg-inline">Big screen</span>
                </a>
            </li>

            <?php if(Session::hasRole(UserRole::USER)): ?>
            <li class="dropdown hidden-xs">
                <a href="/profile" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                    <span class="hidden-lg"><span class="fa fa-ellipsis-v"></span></span>
                    <span class="visible-lg-inline">
                        Profile <span class="caret"></span>
                    </span>
                    <?php if($this->unreadMessageCount > 0): ?>
                    <span class="pm-count flash" title="You have unread messages!"><?= $this->unreadMessageCount; ?></span>
                    <?php endif ?>
                </a>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="/profile">Account</a></li>
                    <li><a href="/profile/messages">Messages</a></li>
                    <li><a href="/profile/authentication">Authentication</a></li>
                    <li class="divider"></li>
                    <li><a href="/logout">Sign Out</a></li>
                </ul>
            </li>
            <?php else: ?>
            <li>
                <a href="/login" rel="login">
                    <span class="fa fa-sign-in"></span>
                    <span class="sign-in-link visible-lg-inline visible-xs-inline">Sign In</span>
                </a>
            </li>
            <?php endif ?>

        </ul>

        <div class="navbar-collapse collapse" id="collapsemenu">
            <ul class="nav navbar-nav">

                <li class="visible-md visible-sm">
                    <a title="Home" href="/">
                        <span class="fa fa-home"></span>
                    </a>
                </li>

                <li class="visible-xs"><a title="Home" href="/">Home</a></li>
                <li class="visible-xs"><a title="Bigscreen" href="/bigscreen">Bigscreen</a></li>

                <?php if(Session::hasRole(UserRole::USER)): ?>
                    <li class="visible-xs"><a title="Your account" href="/profile">Profile</a></li>
                    <li class="divider-vertical visible-xs"></li>
                <?php else: ?>
                    <li class="visible-xs"><a title="Sign in" href="/login">Sign In</a></li>
                    <li class="divider-vertical visible-xs"></li>
                <?php endif ?>

                <li><a title="Blog @ destiny.gg" href="/blog">Blog</a></li>
                <li><a title="twitter.com" href="/twitter">Twitter</a></li>
                <li><a title="youtube.com" href="/youtube">Youtube</a></li>
                <li><a title="reddit.com" href="/reddit">Reddit</a></li>
                <li><a title="facebook.com" href="/facebook">Facebook</a></li>
                <li><a title="Amazon" href="/amazon">Amazon</a></li>

                <?php if(!Session::hasRole(UserRole::SUBSCRIBER)): ?>
                <li class="subscribe"><a href="/subscribe" rel="subscribe" title="Get your own destiny.gg subscription"><span>Subscribe</span></a></li>
                <?php else: ?>
                <li class="subscribed"><a href="/subscribe" rel="subscribe" title="You have an active subscription!"><span>Subscribe</span></a></li>
                <?php endif ?>

                <li><a href="/donate" title="Donate">Donate</a></li>

            </ul>
        </div>
    </div>
</div>