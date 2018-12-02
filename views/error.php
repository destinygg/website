<?php
namespace Destiny;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title(empty($this->code) ? $this->title : Http::$HEADER_STATUSES [$this->code])?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="error" class="error no-brand">
<div id="page-wrap">
    <?php include 'seg/nav.php' ?>
    <?php include 'seg/banner.php' ?>
    <section id="error-container" class="container">

        <div class="mt-3 mb-3" style="text-align: center;">
            <?php if($this->code === Http::STATUS_UNAUTHORIZED): ?>
                <h1 class="display-1">Access denied</h1>
                <p class="lead">You must be authenticated to view this page.
                    <br />Go to the <a href="/login">sign in</a> page
                    <?php if(!empty($this->id)): ?>
                        <br />Support ID <label class="badge badge-danger"><?=Tpl::out($this->id)?></label>
                    <?php endif; ?>
                </p>
            <?php elseif($this->code === Http::STATUS_FORBIDDEN): ?>
                <h1 class="display-1">Access denied</h1>
                <p class="lead">This request has been forbidden.
                    <br />Go to the <a href="/login">sign in</a> page
                    <?php if(!empty($this->id)): ?>
                        <br />Support ID <label class="badge badge-danger"><?=Tpl::out($this->id)?></label>
                    <?php endif; ?>
                </p>
            <?php elseif($this->code === Http::STATUS_NOT_FOUND): ?>
                <h1 class="display-1">Page not found</h1>
                <p class="lead">We could'nt find the page you were looking for.
                    <br />Go to the <a href="/bigscreen">stream</a> page
                    <?php if(!empty($this->id)): ?>
                        <br />Support ID <label class="badge badge-danger"><?=Tpl::out($this->id)?></label>
                    <?php endif; ?>
                </p>
            <?php elseif($this->code === Http::STATUS_SERVICE_UNAVAILABLE): ?>
                <h1 class="display-1">Unavailable</h1>
                <p class="lead">We are doing some work on the site. We will be back shortly.
                    <?php if(!empty($this->id)): ?>
                        <br />Support ID <label class="badge badge-danger"><?=Tpl::out($this->id)?></label>
                    <?php endif; ?>
                </p>
            <?php elseif($this->code === Http::STATUS_ERROR): ?>
                <h1 class="display-1">Error</h1>
                <p class="lead">A application error occurred :(
                    <?php if(!empty($this->id)): ?>
                        <br />Support ID <label class="badge badge-danger"><?=Tpl::out($this->id)?></label>
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <h1 class="display-1">Error</h1>
                <p class="lead">An application error occurred :(
                    <?php if(!empty($this->id)): ?>
                    <br />Support ID <label class="badge badge-danger"><?=Tpl::out($this->id)?></label>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if($this->code >= 500): ?>
            <?php $ini = ini_get('display_errors') ?>
            <?php if($this->error && $this->error instanceof Exception || $ini === '1' || $ini === 'true' || $ini === true || $ini === 1): ?>
                <?php $msg = $this->error->getMessage() ?>
                <?php if(is_string($msg) && strlen(trim($msg)) > 0): ?>
                    <pre><code><?=Tpl::out($msg)?></code></pre>
                <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>

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