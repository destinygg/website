<?php
namespace Destiny;
use Destiny\Common\User\UserRole;
use Destiny\Common\Session\Session;
?>
<div id="main-nav" class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="brand float-left" href="/">Destiny.gg</a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsemenu" aria-controls="collapsemenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="collapsemenu">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item d-block d-sm-none">
                    <a class="nav-link" title="Home" href="/">Home</a>
                </li>
                <li class="nav-item d-block d-sm-none">
                    <a class="nav-link" title="Bigscreen" href="/bigscreen">Bigscreen</a>
                </li>

                <?php if(Session::hasRole(UserRole::USER)): ?>
                    <li class="nav-item d-block d-sm-none">
                        <a class="nav-link" title="Your account" href="/profile">Profile</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item d-block d-sm-none">
                        <a class="nav-link" title="Sign in" href="/login">Sign In</a>
                    </li>
                <?php endif ?>

                <li class="nav-item">
                    <a class="nav-link" title="Youtube" href="/youtube">Youtube</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" title="Instagram" href="/instagram">Instagram</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" title="Reddit" href="/reddit">Reddit</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" title="Facebook" href="/facebook">Facebook</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" title="Positions" href="/positions">Positions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/discord" rel="discord" title="Join discord"><span>Discord</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" title="Donate" href="/donate">Donate</a>
                </li>
                <?php if(!Session::hasRole(UserRole::SUBSCRIBER)): ?>
                    <li class="nav-item subscribe">
                        <a class="nav-link" href="/subscribe" rel="subscribe" title="Get your own subscription"><span>Subscribe</span></a>
                    </li>
                <?php else: ?>
                    <li class="nav-item subscribed">
                        <a class="nav-link" href="/subscribe" rel="subscribe" title="You have an active subscription!"><span>Subscribe</span></a>
                    </li>
                <?php endif ?>

                <li class="nav-item">
                    <a class="nav-link" title="Shop" href="/shop">Shop</a>
                </li>

            </ul>

            <ul class="navbar-nav" id="secondary-navbar">

                <li class="nav-item hidden" id="host-pill">
                    <div id="host-pill-button">
                        <span id="host-pill-type"></span>
                        <span id="host-pill-name"></span>
                        <div class="divider"></div>
                        <div id="host-pill-icon"></div>
                    </div>
                </li>

                <li class="nav-item bigscreen">
                    <a class="nav-link" title="Big screen" href="/bigscreen" rel="bigscreen">
                        <i class="fas fa-fw fa-tv"></i>
                        <span class="nav-label">Big screen</span>
                    </a>
                </li>
                <?php if(Session::hasRole(UserRole::USER)): ?>
                    <li class="nav-item">
                        <a title="Account" class="nav-link" href="/profile">
                            <i class="fas fa-fw fa-user-circle"></i>
                            <span class="nav-label">Account</span>
                        </a>
                    </li>
                <?php elseif(!($hideSignIn ?? false)): ?>
                    <li class="nav-item">
                        <a title="Sign In" class="nav-link" data-toggle="modal" data-target="#loginmodal" rel="login">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="nav-label">Sign In</span>
                        </a>
                    </li>
                <?php endif ?>
                <?php if(Session::hasRole(UserRole::ADMIN)): ?>
                    <li id="admin-link" class="nav-item">
                        <a title="Admin" href="/admin" class="nav-link">
                            <i class="fas fa-shield-alt"></i>
                            <span class="nav-label">Admin</span>
                        </a>
                    </li>
                <?php endif ?>
            </ul>
        </div>

    </div>
</div>
