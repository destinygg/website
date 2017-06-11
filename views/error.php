<?php
namespace Destiny;
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
        <p>An error has occurred.</p>

        <?php if($this->code === Http::STATUS_UNAUTHORIZED): ?>
            <p>You must be authenticated to view this page. Go to the <a href="/login">sign in</a> page</p>
        <?php endif; ?>

        <?php if($this->code === Http::STATUS_FORBIDDEN): ?>
            <p>This request has been forbidden.</p>
        <?php endif; ?>

        <?php if($this->code === Http::STATUS_NOT_FOUND): ?>
            <p>We could'nt find the page you were looking for.</p>
        <?php endif; ?>

        <?php if($this->code === Http::STATUS_ERROR): ?>
            <p>A application error occurred :(.</p>
        <?php endif; ?>

        <?php if($this->code === Http::STATUS_SERVICE_UNAVAILABLE): ?>
            <p>We are doing some work on the site. We will be back shortly.</p>
        <?php endif; ?>

        <?php if($this->error && strlen(trim($this->error->getMessage())) > 0): ?>
        <p><code><?=Tpl::out($this->error->getMessage())?></code></p>
        <?php endif; ?>
    </section>
</div>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/web.js"></script>
</body>
</html>