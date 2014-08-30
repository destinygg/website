<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="orderconfirm">

  <?php include Tpl::file('seg/top.php') ?>
  <?php include Tpl::file('seg/headerband.php') ?>
  
  <section class="container">

    <h1 class="title">
      <span>Subscribe</span> <small>confirm your selection</small>
    </h1>
    
    <div class="content content-dark clearfix">
    
      <?php if(!empty($model->warning)): ?>
      <div style="margin: 15px 15px 0 15px;">
        <div class="alert alert-warning" style="margin: 0;">
          <strong><span class="glyphicon glyphicon-warning-sign"></span> Warning!</strong>
          <?=Tpl::out($model->warning->getMessage())?>
        </div>
      </div>
      <?php endif; ?>

      <div class="ui-step-legend-wrap clearfix">
        <div class="ui-step-legend clearfix">
          <ul>
            <li style="width: 25%;"><a>Select a subscription</a></li>
            <li style="width: 25%;" class="active"><a>Confirmation</a><i class="arrow"></i></li>
            <li style="width: 25%;"><a>Pay subscription</a></li>
            <li style="width: 25%;"><a>Complete</a></li>
          </ul>
        </div>
      </div>

      <div style="width: 100%;" class="clearfix stream">
        <form class="onceoff" action="/subscription/create" method="post">

          <input type="hidden" name="subscription" value="<?= $model->subscriptionType['id'] ?>">
          <input type="hidden" name="gift" value="<?= $model->gift ?>">

          <?php if(!empty($model->currentSubscription)): ?>

          <div class="ds-block clearfix">
          
            <div class="subscriptions pull-left" style="border-right:1px dashed #222; padding-right: 15px; margin-right: 15px;">
              <div class="subscription-tier clearfix">
                <div class="subscription" style="width: auto;">
                  <div><span class="label label-warning">FROM</span></div>
                  <h2><?=$model->currentSubscriptionType['tierLabel']?></h2>
                  <div><span class="sub-amount">$<?=$model->currentSubscriptionType['amount']?></span> (<?=$model->currentSubscriptionType['billingFrequency']?> <?=strtolower($model->currentSubscriptionType['billingPeriod'])?>)</div>
                </div>
              </div>
            </div>
            
            <div class="subscriptions pull-left">
              <div class="subscription-tier clearfix">
                <div class="subscription" style="width: auto;">
                  <div><span class="label label-success">TO</span></div>
                  <h2><?=$model->subscriptionType['tierLabel']?></h2>
                  <div><span class="sub-amount">$<?=$model->subscriptionType['amount']?></span> (<?=$model->subscriptionType['billingFrequency']?> <?=strtolower($model->subscriptionType['billingPeriod'])?>)</div>
                </div>
              </div>
            </div>
            
          </div>

          <?php else: ?>

          <div class="subscription-tier ds-block">
            <div class="subscription">
              <h2><?=$model->subscriptionType['tierLabel']?></h2>

              <?php if(!empty($model->gift)): ?>
              <p><span class="glyphicon glyphicon-gift"></span> You are gifting this to <span class="label label-danger"><?=Tpl::out($model->gift)?></span></p>
              <?php endif; ?>

              <p><span class="sub-amount">$<?=$model->subscriptionType['amount']?></span> (<?=$model->subscriptionType['billingFrequency']?> <?=strtolower($model->subscriptionType['billingPeriod'])?>)</p>
            </div>
          </div>

          <?php endif; ?>

          <div class="ds-block">
            <label class="checkbox">
              <input type="checkbox" name="renew" value="1" checked="checked" /> Automatically renew this subscription
            </label>
          </div>

          <div class="ds-block">
            <p>
              <span>By clicking the 'Pay subscription' button below, you are confirming that this purchase is what you wanted and that you have read the <a href="/help/agreement">user agreement</a>.</span>
              <br /><a href="#" onclick="$(this).hide().parent().next().show(); return false;">Planning to use eChecks?</a>
            </p>
            <p style="display: none;">
              <span class="label label-warning">NOTE:</span> Those who choose to use the &quot;eCheck&quot; as a payment type, will not receive their subscription
              <br>until the payment has been cleared which can take up to 7 business days.
            </p>
          </div>

          <div class="ds-block">
            <label>
              <div>Send a message with your subscription (optional):</div>
              <input type="text" name="sub-message" autocomplete="off" maxlength="250" class="form-control" placeholder=""/>
            </label>
          </div>

          <div class="form-actions">
            <img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
            <button type="submit" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-shopping-cart"></span> Pay subscription</button>
            <a href="/subscribe" class="btn btn-link">Cancel</a>
          </div>

        </form>
      </div>
    </div>
  </section>
  
  <?php include Tpl::file('seg/foot.php') ?>
  <?php include Tpl::file('seg/commonbottom.php') ?>
  
  <script>
  $('form.onceoff').on('submit', function(){
    $(this).find('[type="submit"]').attr("disabled", "disabled");
  });
  </script>
  
</body>
</html>