<?php
namespace Destiny; 
use Destiny\Common\User\UserRole;
use Destiny\Common\Session;
?>
<div id="main-nav" class="navbar navbar-static-top navbar-inverse">
    <div class="container-fluid">
    
        <a class="brand pull-left" href="/">Destiny.gg</a>
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
                    <i class="hidden-xs hidden-sm icon-bigscreen"></i>
                    <span class="">Big screen</span>
                </a>
            </li>
            <li class="divider-vertical"></li>
            <?php if(Session::hasRole(UserRole::USER)): ?>

            <li class="dropdown hidden-xs">
                <a href="/profile" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                    Profile <span class="caret"></span>
                    <?php if($model->unreadMessageCount > 0): ?>
                        <span class="pm-count flash" title="You have unread messages!"><?php echo $model->unreadMessageCount; ?></span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="/profile">Account</a></li>
                    <li><a href="/profile/messages">Messages</a></li>
                    <li><a href="/profile/authentication">Authentication</a></li>
                    <li class="divider"></li>
                    <li><a href="/logout">Sign Out</a></li>
                </ul>
            </li>

            <?php else:?>
            <li>
                <a href="/login" rel="login">
                    <span class="visible-xs fa fa-sign-in"></span>
                    <span class="hidden-xs">Sign In</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="navbar-collapse collapse" id="collapsemenu">
            <ul class="nav navbar-nav">

                <?php if(Session::hasRole(UserRole::USER)): ?>
                <li class="visible-xs"><a title="Your account" href="/profile">Profile</a></li>
                <li class="divider-vertical visible-xs"></li>
                <?php endif; ?>

                <li><a title="Blog @ destiny.gg" href="//blog.destiny.gg">Blog</a></li>
                <li><a title="twitter.com" href="//twitter.com/Steven_Bonnell/">Twitter</a></li>
                <li><a title="youtube.com" href="//www.youtube.com/user/Destiny">Youtube</a></li>
                <li><a title="reddit.com" href="//www.reddit.com/r/Destiny/">Reddit</a></li>
                <li><a title="facebook.com" href="//www.facebook.com/Steven.Bonnell.II">Facebook</a></li>

                <?if(!Session::hasRole(UserRole::SUBSCRIBER)):?>
                <li class="subscribe"><a href="/subscribe" rel="subscribe" title="Get your own destiny.gg subscription"><span>Subscribe Now!</span></a></li>
                <?php endif; ?>

                <?if(Session::hasRole(UserRole::SUBSCRIBER)):?>
                <li class="subscribed"><a href="/subscribe" rel="subscribe" title="You have an active subscription!"><span>Subscribe</span></a></li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</div>