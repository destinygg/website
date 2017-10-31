<?php
namespace Destiny;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title(empty($this->code) ? $this->title : Http::$HEADER_STATUSES [$this->code])?></title>
<?php include 'seg/meta.php' ?>
<link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="error" class="error no-brand">
<div id="page-wrap">
    <?php include 'seg/nav.php' ?>
    <?php include 'seg/banner.php' ?>
    <section id="error-container" class="container">
        <a title="Rick and Morty" href="http://www.adultswim.com/videos/rick-and-morty/" target="_blank" id="mortyface"></a>
        <h1>Aw geez, Rick!</h1>

        <p>
            <?php if($this->code === Http::STATUS_UNAUTHORIZED): ?>
            You must be authenticated to view this page. Go to the <a href="/login">sign in</a> page
            <?php elseif($this->code === Http::STATUS_FORBIDDEN): ?>
            This request has been forbidden.
            <?php elseif($this->code === Http::STATUS_NOT_FOUND): ?>
            We could'nt find the page you were looking for.
            <?php elseif($this->code === Http::STATUS_ERROR): ?>
            A application error occurred :(
            <?php elseif($this->code === Http::STATUS_SERVICE_UNAVAILABLE): ?>
            We are doing some work on the site. We will be back shortly.
            <?php else: ?>
            Something went really bad, call help...
            <?php endif; ?>
            <br />
            <?php if(!empty($this->id)): ?>
            Include this ID in your support query <label class="label label-danger"><?=Tpl::out($this->id)?></label>
            <?php endif; ?>
        </p>

        <?php $ini = @ini_get('display_errors') ?>
        <?php if($this->error  && $this->error instanceof Exception || $ini === '1' || $ini === 'true' || $ini === true || $ini === 1): ?>
        <?php $msg = $this->error->getMessage() ?>
        <?php if(is_string($msg) && strlen($msg) > 0): ?>
        <p><code><?=Tpl::out(trim($this->error->getMessage()))?></code></p>
        <?php endif; ?>
        <?php endif; ?>

    </section>
</div>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/web.js"></script>
</body>
</html>