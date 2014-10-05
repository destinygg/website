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
        <a title="Menu" href="#" class="collapsed" aria-label="Menu" data-toggle="collapse"
            data-target="#collapsemenu">
          <span class="menuicon"></span>
        </a>
      </li>
    </ul>

    <div class="navbar-header pull-right">
      <ul class="nav navbar-nav">
        <?if(!Session::hasRole(UserRole::SUBSCRIBER)):?>
        <li class="divider-vertical"></li>
        <li class="subscribe"><a href="/subscribe" rel="subscribe" title="Get your own destiny.gg subscription"><span>Subscribe</span></a></li>
        <?php endif; ?>

        <?if(Session::hasRole(UserRole::SUBSCRIBER)):?>
        <li class="divider-vertical"></li>
        <li class="subscribed"><a href="/subscribe" rel="subscribe" title="You have an active subscription!"><span>Subscribe</span></a></li>
        <?php endif; ?>

        <li class="bigscreen"><a title="So. Much. Girth." href="/bigscreen" rel="bigscreen"><i class="icon-bigscreen"></i><span class="hidden-xs">Big screen</span></a></li>
        <li class="divider-vertical"></li>
        <?php if(Session::hasRole(UserRole::USER)): ?>
        <li>
          <a href="/profile" rel="profile">
            <span class="visible-xs glyphicon glyphicon-user"></span>
            <span class="hidden-xs">Profile</span>
          </a>
        </li>
        <li>
          <a href="/logout" title="Sign out">
            <span class="glyphicon glyphicon-off"></span>
          </a>
        </li>
        <?php else:?>
        <li>
          <a href="/login" rel="login">
            <span class="visible-xs glyphicon glyphicon-log-in"></span>
            <span class="hidden-xs">Sign In</span>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="navbar-collapse collapse" id="collapsemenu">
      <ul class="nav navbar-nav">
        <li><a title="Blog @ destiny.gg" href="http://blog.destiny.gg">Blog</a></li>
        <li><a title="twitter.com" href="https://twitter.com/Steven_Bonnell/">Twitter</a></li>
        <li><a title="youtube.com" href="http://www.youtube.com/user/Destiny">Youtube</a></li>
        <li><a title="reddit.com" href="http://www.reddit.com/r/Destiny/">Reddit</a></li>
        <li><a title="facebook.com" href="https://www.facebook.com/Steven.Bonnell.II">Facebook</a></li>
        <li><a title="Schedule" href="/schedule">Schedule</a></li>
      </ul>
    </div>
  </div>
</div>
