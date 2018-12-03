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
                    <a class="nav-link" title="Blog" href="/blog">Blog</a>
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
                    <a class="nav-link" title="Shop" href="/shop">
                        <span>Shop</span>
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                </li>

            </ul>

            <ul class="navbar-nav">
                <li class="nav-item bigscreen">
                    <a class="nav-link" title="So. Much. Girth." href="/bigscreen" rel="bigscreen">
                        <i class="fas fa-tv"></i>
                        <span>Big screen</span>
                    </a>
                </li>
                <?php if(Session::hasRole(UserRole::USER)): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/profile">
                            <i class="fas fa-user-circle"></i>
                            <span>Account</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="modal" data-target="#loginmodal" rel="login">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Sign In</span>
                        </a>
                    </li>
                <?php endif ?>
            </ul>
        </div>

    </div>
</div>