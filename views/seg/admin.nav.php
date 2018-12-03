<?php
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
?>
<section class="container">
    <ol class="breadcrumb" style="margin-bottom:0; font-size: 110%;">

        <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i></a></li>

        <?php if(Session::hasRole(UserRole::MODERATOR)): ?>
            <li class="breadcrumb-item"><a href="/admin/users">Users</a></li>
            <li class="breadcrumb-item"><a href="/admin/bans">Bans</a></li>
            <li class="breadcrumb-item"><a href="/admin/chat">Chat</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::FINANCE)): ?>
            <li class="breadcrumb-item"><a href="/admin/subscribers">Subscribers</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::ADMIN)): ?>
            <li class="breadcrumb-item"><a href="/admin/streamlabs">StreamLabs</a></li>
            <li class="breadcrumb-item"><a href="/admin/twitch">Twitch</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::EMOTES)): ?>
            <li class="breadcrumb-item"><a href="/admin/emotes">Emotes</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::FLAIRS)): ?>
            <li class="breadcrumb-item"><a href="/admin/flairs">Flairs</a></li>
        <?php endif; ?>

    </ol>
</section>