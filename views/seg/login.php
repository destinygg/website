<?php
namespace Destiny;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
?>
<?php if(!Session::hasRole(UserRole::USER)): ?>
<div class="modal" id="loginmodal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form action="/login" method="post">
                    <input type="hidden" name="follow" value="" />
                    <input type="hidden" name="grant" value="" />
                    <input type="hidden" name="authProvider" class="hidden" />
                    <div id="loginproviders">
                        <a class="btn btn-lg btn-twitch" tabindex="1" data-provider="twitch">
                            <i class="fab fa-twitch"></i> Twitch
                        </a>
                        <a class="btn btn-lg btn-google" tabindex="2" data-provider="google">
                            <i class="fab fa-google"></i> Google
                        </a>
                        <a class="btn btn-lg btn-twitter" tabindex="2" data-provider="twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a class="btn btn-lg btn-reddit" tabindex="2" data-provider="reddit">
                            <i class="fab fa-reddit"></i> Reddit
                        </a>
                        <a class="btn btn-lg btn-discord" tabindex="2" data-provider="discord">
                            <i class="fab fa-discord"></i> Discord
                        </a>
                    </div>
                    <div class="form-group form-group-remember-me">
                        <div class="controls checkbox">
                            <label>
                                <input tabindex="1" autofocus type="checkbox" name="rememberme" <?=($this->rememberme) ? 'checked':''?>> Remember me
                            </label>
                            <span class="help-block">(this should only be used if you are on a private computer)</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>