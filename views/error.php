<?php
namespace Destiny;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
    <?=Tpl::title(empty($this->code) ? $this->title : Http::$HEADER_STATUSES [$this->code])?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('common.vendor.css')?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="error" class="error no-brand">
<div id="page-wrap">
    <?php include 'seg/nav.php' ?>
    <?php include 'seg/banner.php' ?>
    <section id="error-container" class="container">

        <?php if($this->code === Http::STATUS_UNAUTHORIZED): ?>
            <h1>Access denied</h1>
            <p>You must be authenticated to view this page. Go to the <a href="/login">sign in</a> page</p>
        <?php elseif($this->code === Http::STATUS_FORBIDDEN): ?>
            <h1>Access denied</h1>
            <p>This request has been forbidden.</p>
        <?php elseif($this->code === Http::STATUS_NOT_FOUND): ?>
            <h1>Page not found</h1>
            <p>We could'nt find the page you were looking for.</p>
        <?php elseif($this->code === Http::STATUS_ERROR): ?>
            <h1>Application Error</h1>
            <p>A application error occurred :(</p>
        <?php elseif($this->code === Http::STATUS_SERVICE_UNAVAILABLE): ?>
            <h1>Application Error</h1>
            <p>We are doing some work on the site. We will be back shortly.</p>
        <?php else: ?>
            <h1>Application Error</h1>
            <p>Something went really bad, call help...</p>
        <?php endif; ?>
        <?php if(!empty($this->id)): ?>
            <p>Include this ID in your support query <label class="label label-danger"><?=Tpl::out($this->id)?></label></p>
        <?php endif; ?>

        <?php $ini = @ini_get('display_errors') ?>
        <?php if($this->error && $this->error instanceof Exception || $ini === '1' || $ini === 'true' || $ini === true || $ini === 1): ?>
            <?php $msg = $this->error->getMessage() ?>
            <?php if(is_string($msg) && strlen(trim($msg)) > 0): ?>
                <p><code><?=Tpl::out($msg)?></code></p>
            <?php endif; ?>
        <?php endif; ?>

    </section>
</div>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
</body>
</html>