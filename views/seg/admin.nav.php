<?php
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
?>
<section class="container">
    <ol class="breadcrumb" style="margin-bottom:0; font-size: 110%;">

        <li><a href="/admin"><span class="fa fa-home fa-fw"></span></a></li>

        <?php if(Session::hasRole(UserRole::MODERATOR)): ?>
            <li><a href="/admin/users">Users</a></li>
            <li><a href="/admin/bans">Bans</a></li>
            <li><a href="/admin/chat">Chat</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::FINANCE)): ?>
            <li><a href="/admin/subscribers">Subscribers</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::ADMIN)): ?>
            <li><a href="/admin/streamlabs">StreamLabs</a></li>
            <li><a href="/admin/twitch">Twitch</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::EMOTES)): ?>
            <li><a href="/admin/emotes">Emotes</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::FLAIRS)): ?>
            <li><a href="/admin/flairs">Flairs</a></li>
        <?php endif; ?>

    </ol>
</section>