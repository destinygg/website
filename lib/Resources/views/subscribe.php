<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="subscribe">
  <div id="page-wrap">

    <?php include Tpl::file('seg/top.php') ?>
    <?php include Tpl::file('seg/headerband.php') ?>
    
    <section class="container">
    
      <?php if(Session::hasRole(UserRole::USER)): ?>
      <div id="giftSubscriptionSelect" class="alert alert-info" style="text-align: center;">
          Would you like to gift someone a subscription? 
          <button class="btn btn-primary" data-toggle="modal" data-target="#userSearchModal">Yes, gift a subscription <span class="fa fa-gift"></span></button>
      </div>

      <div id="giftSubscriptionConfirm" class="alert alert-info hidden" style="text-align: center;">
          You are gifting your subscription to <strong id="subscriptionGiftUsername"></strong>!
          <button class="btn btn-primary" id="selectGiftSubscription" data-toggle="modal" data-target="#userSearchModal">Change <span class="fa fa-gift"></span></button>
          <button class="btn btn-default" id="cancelGiftSubscription">Abort!</button>
      </div>
      <? endif; ?>

      <div class="subfeature">
        <div class="subfeature-desc">
          <h1>Tier IV</h1>
          <p>Know in your heart you have made the right choice here.</p>
        </div>
        <div class="subfeature-options clearfix">
          <div class="subfeature-t1">
            <?php $sub = $model->subscriptions['1-MONTH-SUB4']?>
            <form action="/subscription/confirm" method="post">
              <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
              <input type="hidden" name="gift" value="" />
              <input type="hidden" name="gift-message" value="" />
              <div class="subfeature-info">
                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
              </div>
              <div class="subfeature-info">
                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                <button type="submit" class="btn btn-primary">Choose</button>
              </div>
            </form>
          </div>
          <div class="subfeature-t2">
            <?php $sub = $model->subscriptions['3-MONTH-SUB4']?>
            <form action="/subscription/confirm" method="post">
              <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
              <input type="hidden" name="gift" value="" />
              <input type="hidden" name="gift-message" value="" />
              <div class="subfeature-info">
                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
              </div>
              <div class="subfeature-info">
                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                <button type="submit" class="btn btn-primary">Choose</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="subfeature active">
        <div class="subfeature-desc">
          <h1>Tier III</h1>
          <p>Wow such value so prestige you should purchase immediately.</p>
        </div>
        <div class="subfeature-options clearfix">
          <div class="subfeature-t1">
            <?php $sub = $model->subscriptions['1-MONTH-SUB3']?>
            <form action="/subscription/confirm" method="post">
              <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
              <input type="hidden" name="gift" value="" />
              <input type="hidden" name="gift-message" value="" />
              <div class="subfeature-info">
                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
              </div>
              <div class="subfeature-info">
                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                <button type="submit" class="btn btn-primary">Choose</button>
              </div>
            </form>
          </div>
          <div class="subfeature-t2">
            <?php $sub = $model->subscriptions['3-MONTH-SUB3']?>
            <form action="/subscription/confirm" method="post">
              <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
              <input type="hidden" name="gift" value="" />
              <input type="hidden" name="gift-message" value="" />
              <div class="subfeature-info">
                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
              </div>
              <div class="subfeature-info">
                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                <button type="submit" class="btn btn-primary">Choose</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="subfeature">
        <div class="subfeature-desc">
          <h1>Tier II</h1>
          <p>Got a bit more to contribute? Probably the best investment of all time.</p>
        </div>
        <div class="subfeature-options clearfix">
          <div class="subfeature-t1">
            <?php $sub = $model->subscriptions['1-MONTH-SUB2']?>
            <form action="/subscription/confirm" method="post">
              <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
              <input type="hidden" name="gift" value="" />
              <input type="hidden" name="gift-message" value="" />
              <div class="subfeature-info">
                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
              </div>
              <div class="subfeature-info">
                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                <button type="submit" class="btn btn-primary">Choose</button>
              </div>
            </form>
          </div>
          <div class="subfeature-t2">
            <?php $sub = $model->subscriptions['3-MONTH-SUB2']?>
            <form action="/subscription/confirm" method="post">
              <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
              <input type="hidden" name="gift" value="" />
              <input type="hidden" name="gift-message" value="" />
              <div class="subfeature-info">
                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
              </div>
              <div class="subfeature-info">
                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                <button type="submit" class="btn btn-primary">Choose</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="subfeature">
       <div class="subfeature-desc">
          <h1>Tier I</h1>
          <p>Get access to chat features and be eligible for future subscriber events!</p>
        </div>
        <div class="subfeature-options clearfix">
          <div class="subfeature-t1">
            <?php $sub = $model->subscriptions['1-MONTH-SUB']?>
            <form action="/subscription/confirm" method="post">
              <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
              <input type="hidden" name="gift" value="" />
              <input type="hidden" name="gift-message" value="" />
              <div class="subfeature-info">
                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
              </div>
              <div class="subfeature-info">
                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                <button type="submit" class="btn btn-primary">Choose</button>
              </div>
            </form>
          </div>
          <div class="subfeature-t2">
            <?php $sub = $model->subscriptions['3-MONTH-SUB']?>
            <form action="/subscription/confirm" method="post">
              <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
              <input type="hidden" name="gift" value="" />
              <input type="hidden" name="gift-message" value="" />
              <div class="subfeature-info">
                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
              </div>
              <div class="subfeature-info">
                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                <button type="submit" class="btn btn-primary">Choose</button>
              </div>
            </form>
          </div>
        </div>
      </div>
        
    </section>

    <div class="modal fade" id="userSearchModal" tabindex="-1" role="dialog" aria-labelledby="userSearchModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <form id="userSearchForm">
            
              <div id="usrFrmGrp" class="form-group">
                <label>Who do you want to gift?</label>
                <input tabindex="1" type="text" id="userSearchInput" class="form-control" placeholder="E.g. Destiny" />
                <label class="error hidden"></label>
              </div>
            
              <button tabindex="4" type="button" class="btn btn-default" data-dismiss="modal" id="userSearchCancel">Cancel</button>
              <button tabindex="3" type="submit" class="btn btn-primary" id="userSearchSelect" data-loading-text="Checking user...">Select</button>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
  
  <?php include Tpl::file('seg/foot.php') ?>
  <?php include Tpl::file('seg/commonbottom.php') ?>
  
</body>
</html>