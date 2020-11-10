<?php
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
?>
<section class="container">
    <ol class="breadcrumb" style="margin-bottom:0; font-size: 110%;">

        <?php if(Session::hasRole(UserRole::MODERATOR)): ?>
            <li class="breadcrumb-item"><a href="/admin/moderation">Moderation</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">Users</a></li>
            <li class="breadcrumb-item"><a href="/admin/bans">Bans</a></li>
            <li class="breadcrumb-item"><a href="/admin/chat">Chat</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::FINANCE)): ?>
            <li class="breadcrumb-item"><a href="/admin/income">Income</a></li>
            <li class="breadcrumb-item"><a href="/admin/subscriptions">Subscriptions</a></li>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::STREAMLABS) || Session::hasRole(UserRole::STREAMELEMENTS) || Session::hasRole(UserRole::ADMIN)): ?>
            <?php if(Session::hasRole(UserRole::STREAMLABS)): ?>
                <li class="breadcrumb-item"><a href="/admin/streamlabs">StreamLabs</a></li>
            <?php endif; ?>
            <?php if(Session::hasRole(UserRole::STREAMELEMENTS)): ?>
                <li class="breadcrumb-item"><a href="/admin/streamelements">StreamElements</a></li>
            <?php endif; ?>
            <?php if(Session::hasRole(UserRole::ADMIN)): ?>
                <li class="breadcrumb-item"><a href="/admin/twitch">Twitch</a></li>
                <li class="breadcrumb-item"><a href="/admin/youtube">YouTube</a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(Session::hasRole(UserRole::MODERATOR)): ?>
            <li class="breadcrumb-item"><a href="/admin/audit">Audit</a></li>
        <?php endif; ?>


        <?php if(Session::hasRole(UserRole::FLAIRS) || Session::hasRole(UserRole::EMOTES)): ?>
            <?php if(Session::hasRole(UserRole::EMOTES)): ?>
            <li class="breadcrumb-item"><a href="/admin/emotes">Emotes</a></li>
            <?php endif; ?>
            <?php if(Session::hasRole(UserRole::FLAIRS)): ?>
                <li class="breadcrumb-item"><a href="/admin/flairs">Flairs</a></li>
            <?php endif; ?>
            <li class="breadcrumb-item"><a href="/admin/themes">Themes</a></li>
        <?php endif; ?>

    </ol>
</section>