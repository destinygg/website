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
                    <i class="icon-tv"></i>
                    <span class="visible-lg-inline">Big screen</span>
                </a>
            </li>

            <?php if(Session::hasRole(UserRole::USER)): ?>
            <li class="hidden-xs">
                <a href="/profile">
                    <span class="fa fa-user-circle"></span>
                    <span class="visible-lg-inline visible-xs-inline">Account</span>
                </a>
            </li>
            <?php else: ?>
            <li>
                <a data-toggle="modal" data-target="#loginmodal" rel="login">
                    <span class="fa fa-sign-in"></span>
                    <span class="visible-lg-inline visible-xs-inline">Sign In</span>
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

                <li><a title="Blog" href="/blog">Blog</a></li>
                <li><a title="twitter.com" href="/twitter">Twitter</a></li>
                <li><a title="youtube.com" href="/youtube">Youtube</a></li>
                <li><a title="reddit.com" href="/reddit">Reddit</a></li>
                <li><a title="facebook.com" href="/facebook">Facebook</a></li>
                <li><a title="Donate" href="/donate">Donate</a></li>

                <?php if(!Session::hasRole(UserRole::SUBSCRIBER)): ?>
                    <li class="subscribe"><a href="/subscribe" rel="subscribe" title="Get your own subscription"><span>Subscribe</span></a></li>
                <?php else: ?>
                    <li class="subscribed"><a href="/subscribe" rel="subscribe" title="You have an active subscription!"><span>Subscribe</span></a></li>
                <?php endif ?>

            </ul>
        </div>
    </div>
</div>