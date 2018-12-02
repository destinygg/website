<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="authentication" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/alerts.php' ?>
    <?php include 'menu.php' ?>

    <section class="container">
        <h3 data-toggle="collapse" data-target="#authentication-content">Providers</h3>
        <div id="authentication-content" class="content content-dark collapse show">
            <div class="ds-block">
                <p>Connect all the providers to the same destiny.gg user.</p>
            </div>
            <form id="auth-profile-form" method="post">
                <table class="grid" style="width:100%">
                    <tbody>
                    <?php foreach(Config::$a ['authProfiles'] as $id): ?>
                        <tr>
                            <td><?=ucwords($id)?></td>
                            <td style="width:100%;">
                                <?php if(in_array($id, $this->authProfileTypes)): ?>
                                    <a href="/profile/remove/<?=$id?>" data-confirm="Are you sure?" class="btn btn-danger btn-sm btn-post">Remove</a>
                                <?php else: ?>
                                    <a href="/profile/connect/<?=$id?>" class="btn btn-primary btn-sm btn-post">Connect</a>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            <br />
        </div>
    </section>

</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>

</body>
</html>