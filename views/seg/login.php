<?php
namespace Destiny;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
?>
<?php if(!Session::hasRole(UserRole::USER)): ?>
<div id="loginmodal" class="modal fade">
    <form class="modal-dialog" action="/login" method="post">
        <input type="hidden" name="follow" value="" />
        <input type="hidden" name="grant" value="" />
        <input type="hidden" name="authProvider" class="hidden" />
        <div class="modal-body">
            <div class="form-group">
                <div class="controls checkbox">
                    <label>
                        <input tabindex="1" autofocus type="checkbox" name="rememberme" <?=($this->rememberme) ? 'checked':''?>> Remember me
                    </label>
                    <span class="help-block">(this should only be used if you are on a private computer)</span>
                </div>
            </div>
            <div id="loginproviders">
                <a class="btn btn-lg btn-twitch" tabindex="1" data-provider="twitch">
                    <i class="fa fa-twitch"></i> Twitch
                </a>
                <a class="btn btn-lg btn-google" tabindex="2" data-provider="google">
                    <i class="fa fa-google"></i> Google
                </a>
                <a class="btn btn-lg btn-twitter" tabindex="2" data-provider="twitter">
                    <i class="fa fa-twitter"></i> Twitter
                </a>
                <a class="btn btn-lg btn-reddit" tabindex="2" data-provider="reddit">
                    <i class="fa fa-reddit"></i> Reddit
                </a>
                <a class="btn btn-lg btn-discord" tabindex="2" data-provider="discord">
                    <i class="fa fa-discord"></i> Discord
                </a>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>