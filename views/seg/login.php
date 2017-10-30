<?php
namespace Destiny;
use Destiny\Common\Config;
?>

<div id="loginmodal" class="modal fade">
    <form class="modal-dialog" action="/login" method="post">
        <input type="hidden" name="follow" value="" />
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
                <?php foreach (Config::$a['authProfiles'] as $i => $id): ?>
                <a class="btn btn-lg btn-<?=$id?>" tabindex="<?=$i+1?>" data-provider="<?=$id?>">
                    <i class="fa fa-<?=$id?>"></i> <?=ucwords($id)?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </form>
</div>
