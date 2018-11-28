<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('common.vendor.css')?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="developer" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/alerts.php' ?>
    <?php include 'menu.php' ?>

    <section class="container active">
        <h3 class="collapsed" data-toggle="collapse" data-target="#app-content">Applications</h3>
        <div id="app-content" class="content content-dark clearfix collapse">
            <div class="ds-block">
                <p>Only one application allowed per account. (for now)<br />
                    Integrate you application with DGG users.</p>
            </div>
            <?php if(!empty($this->oauthClients)): ?>
                <form id="app-form" action="/profile/app/update" method="post" role="form">
                    <?php foreach($this->oauthClients as $authClient): ?>
                    <input type="hidden" name="id" value="<?=Tpl::out($authClient['clientId'])?>" />
                    <div class="ds-block">
                        <div class="form-group">
                            <label>Name:</label>
                            <input class="form-control" type="text" name="name" value="<?=Tpl::out($authClient['clientName'])?>" placeholder="Name" />
                        </div>
                        <div class="form-group">
                            <label>ID:</label>
                            <input class="form-control" type="text" readonly="readonly" name="code" value="<?=Tpl::out($authClient['clientCode'])?>" placeholder="Code" />
                        </div>
                        <div class="form-group">
                            <label>Secret <a id="app-form-secret-create" data-id="<?=$authClient['clientId']?>" href="#">(create)</a>:</label>
                            <input class="form-control" type="text" readonly="readonly" name="secret" value="" placeholder="************" />
                        </div>
                    </div>
                    <div class="form-actions block-foot">
                        <button class="btn btn-primary" type="submit">Save App</button>
                        <a href="/profile/app/<?=$authClient['clientId']?>/remove" data-confirm="Are you sure?" class="btn btn-danger btn-post">Delete App</a>
                    </div>
                    <?php break; ?>
                    <?php endforeach; ?>
                </form>
            <?php else: ?>
                <form id="app-form" action="/profile/app/create" method="post" role="form">
                    <div class="ds-block">
                        <div class="form-group">
                            <label>Name:</label>
                            <input class="form-control input-lg" type="text" name="name" value="<?=Tpl::out($this->user['username'])?> App" placeholder="Name" />
                        </div>
                    </div>
                    <div id="recaptcha1" class="form-group ds-block hidden">
                        <div class="controls">
                            <div class="g-recaptcha" data-sitekey="<?=Tpl::out(Config::$a['g-recaptcha']['key'])?>"></div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" id="btn-create-app">Create new app</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <section class="container active">
        <h3 class="collapsed" data-toggle="collapse" data-target="#login-key-content">Connections</h3>
        <div id="login-key-content" class="content content-dark clearfix collapse">
            <div class="ds-block">
                <p>Login keys allow <strong>you</strong> to authenticate without the need for a username or password.<br />
                    DGG chat will authenticate your user when you connect to the chat server with a cookie <strong>authtoken</strong> with the token as the value.<br />
                    <strong>IMPORTANT</strong> these keys must be kept private.</p>
            </div>
            <form id="authtoken-form" action="/profile/authtoken/create" method="post">
                <?php if(!empty($this->accessTokens)): ?>
                <table class="grid" style="width:100%">
                    <thead>
                    <tr>
                        <td style="width:100%;">Application</td>
                        <td>Created</td>
                        <td></td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($this->accessTokens as $token): ?>
                        <tr>
                            <td>
                                <?php if (!empty($token['clientName'])): ?>
                                    <span><?=$token['clientName']?></span>
                                <?php else: ?>
                                    <span>DGG Login Key</span>
                                    <a href="#token-<?=$token['tokenId']?>" data-toggle="show">(show)</a> <code class="collapse" id="token-<?=$token['tokenId']?>"><?=Tpl::out($token['token'])?></code>
                                <?php endif; ?>
                            </td>
                            <td><?=Tpl::moment(Date::getDateTime($token['createdDate']), Date::STRING_FORMAT)?></td>
                            <td><a href="/profile/authtoken/<?=$token['tokenId']?>/delete" data-confirm="Are you sure?" class="btn btn-danger btn-xs btn-post">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif ?>
                <div id="recaptcha2" class="form-group ds-block hidden">
                    <div class="controls">
                        <div class="g-recaptcha" data-sitekey="<?=Tpl::out(Config::$a['g-recaptcha']['key'])?>"></div>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="btn-create-key">Add login key</button>
                </div>
            </form>
        </div>
    </section>

</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<script src="https://www.google.com/recaptcha/api.js"></script>

</body>
</html>