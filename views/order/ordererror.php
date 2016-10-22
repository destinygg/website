<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta charset="utf-8">
<?php include 'seg/commontop.php' ?>
<?php include 'seg/google.tracker.php' ?>
</head>
<body id="ordererror" class="no-brand">
  <div id="page-wrap">

    <?php include 'seg/top.php' ?>
    <?php include 'seg/headerband.php' ?>
    
    <section class="container">

      <h1 class="title">
        <span>Subscribe</span> <small>become one of the brave</small>
      </h1>

      <div class="content content-dark clearfix">

        <div class="ui-step-legend-wrap clearfix">
          <div class="ui-step-legend clearfix">
            <ul>
              <li style="width: 25%;"><a>Select a subscription</a></li>
              <li style="width: 25%;"><a>Confirmation</a></li>
              <li style="width: 25%;"><a>Pay subscription</a></li>
              <li style="width: 25%;"><a>Complete</a></li>
            </ul>
          </div>
        </div>

        <div class="ds-block">

          <p>An error has occurred during the subscription process.

          <?php if(!empty($this->subscription)): ?>
          <br />Your reference is #<?=Tpl::out($this->subscription['subscriptionId'])?>
          <?php endif ?>

          <br>Please start again or email <a href="mailto:<?=Config::$a['paypal']['support_email']?>"><?=Config::$a['paypal']['support_email']?></a> for queries.
          </p>

          <?php if(!empty($this->error)): ?>
          <div class="alert alert-danger">
            <strong>Error!</strong>
            <?=Tpl::out($this->error->getMessage())?>
          </div>
          <?php endif ?>

        </div>

        <div class="form-actions">
          <img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
          <a href="/subscribe" class="btn btn-link">Subscriptions</a>
          <a href="/profile" class="btn btn-link">Back to profile</a>
        </div>

      </div>
    </section>
  </div>
  
  <?php include 'seg/foot.php' ?>
  <?php include 'seg/commonbottom.php' ?>
  
</body>
</html>