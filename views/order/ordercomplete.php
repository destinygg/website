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
<link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="ordercomplete" class="no-brand">
  <div id="page-wrap">

    <?php include 'seg/top.php' ?>
    <?php include 'seg/headerband.php' ?>
    
    <section class="container">

      <h1 class="title">
        <span>Complete</span> <small>successful</small>
      </h1>

      <div class="content content-dark clearfix">

        <div class="ui-step-legend-wrap clearfix">
          <div class="ui-step-legend clearfix">
            <ul>
              <li style="width: 25%;"><a>Select a subscription</a></li>
              <li style="width: 25%;"><a>Confirmation</a></li>
              <li style="width: 25%;"><a>Pay subscription</a></li>
              <li style="width: 25%;" class="active"><a>Complete</a><i class="arrow"></i></li>
            </ul>
          </div>
        </div>

        <form action="/" method="GET">
        
          <div class="ds-block">
            <p>Your order was successful, The order reference is <span class="label label-default">#<?=$this->subscription['subscriptionId']?></span>
            <br />Please email <a href="mailto:<?=Config::$a['paypal']['support_email']?>"><?=Config::$a['paypal']['support_email']?></a> for any queries or issues.
            <br /><br />Thank you for your support!</p>
          </div>
                
          <div class="subscription-tier ds-block">

            <div class="subscription" style="width: auto;">
              <h3><?=$this->subscriptionType['tierLabel']?></h3>
              <p><span class="sub-amount">$<?=$this->subscriptionType['amount']?></span> (<?=$this->subscriptionType['billingFrequency']?> <?=strtolower($this->subscriptionType['billingPeriod'])?>)</p>
              
              <?php if($this->subscription['recurring'] == 1): ?>
              <p>Subscription is automatically renewed</p>
              <?php endif ?>

              <?php if(!empty($this->giftee)): ?>
              <p><span class="fa fa-gift"></span> You have gifted this to <span class="label label-danger"><?=Tpl::out($this->giftee['username'])?></span></p>
              <?php endif ?>

            </div>

          </div>
          
          <div class="form-actions">
            <a class="pull-right powered-paypal" title="Powered by Paypal" href="https://www.paypal.com" target="_blank">Paypal</a>
            <a href="/profile" class="btn btn-primary">Back to profile</a>
          </div>

        </form>

      </div>
    </section>
  </div>
  
  <?php include 'seg/foot.php' ?>
  <?php include 'seg/commonbottom.php' ?>
  <script src="<?=Config::cdnv()?>/web.js"></script>
  
</body>
</html>