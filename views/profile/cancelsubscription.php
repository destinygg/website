<?php
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta charset="utf-8">
<?php include 'seg/commontop.php' ?>
<link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="subscription" class="no-contain">
  <div id="page-wrap">

    <?php include 'seg/top.php' ?>

    <section class="container">
      <h1 class="page-title">
        <span>Cancel</span>
        <small>subscription</small>
      </h1>
    </section>

    <section class="container">
      <div class="content content-dark clearfix">

        <?php if($this->subscriptionCancelled): ?>
        <div class="ds-block">
          <div class="form-group">
            <p>
              <label class="label label-success">SUCCESS</label> Your subscription has been cancelled.
              Thank you for your support!
            </p>
          </div>
          <div class="form-group">
            <dl class="dl-horizontal">
              <dt>Status:</dt>

              <dd>
                <span class="label label-<?=($this->subscription['status'] == 'Active') ? 'success':'warning'?>"><?=Tpl::out($this->subscription['status'])?></span>
                <?php if($this->subscription['recurring']):?>
                <span class="label label-warning" title="This subscription is automatically renewed">Recurring</span>
                <?php else: ?>
                <span class="label label-default" title="This subscription is not automatically renewed">Not recurring</span>
                <?php endif ?>
              </dd>

              <dt>Time remaining:</dt>
              <dd><?=Date::getRemainingTime(Date::getDateTime($this->subscription['endDate']))?></dd>
              <dt>Created date:</dt>
              <dd><?=Tpl::moment(Date::getDateTime($this->subscription['createdDate']), Date::STRING_FORMAT_YEAR)?></dd>
              <dt>End date:</dt>
              <dd><?=Tpl::moment(Date::getDateTime($this->subscription['endDate']), Date::STRING_FORMAT_YEAR)?></dd>

              <?php if(!empty($this->giftee)): ?>
              <dt>Gifted to:</dt>
              <dd><?=Tpl::out( $this->giftee['username'] )?></dd>
              <?php endif ?>

            </dl>
          </div>
        </div>
        <div class="form-actions">
          <a class="btn btn-link" href="/profile">Back to profile</a>
        </div>
        <?php endif ?>

        <?php if(!$this->subscriptionCancelled): ?>
        <form action="/subscription/cancel" method="post" autocomplete="off">

          <input type="hidden" name="subscriptionId" value="<?=Tpl::out($this->subscription['subscriptionId'])?>" />

          <div class="ds-block">
            <div class="form-group">
              <dl class="dl-horizontal">
                <dt>Status:</dt>
                <dd>
                  <span class="label label-<?=($this->subscription['status'] == 'Active') ? 'success':'warning'?>"><?=Tpl::out($this->subscription['status'])?></span>
                  <?php if($this->subscription['recurring']):?>
                  <span class="label label-warning" title="This subscription is automatically renewed">Recurring</span>
                  <?php endif ?>
                </dd>

                <dt>Source:</dt>
                <dd><?=Tpl::out($this->subscription['subscriptionSource'])?></dd>
                <dt>Created date:</dt>
                <dd><?=Tpl::moment(Date::getDateTime($this->subscription['createdDate']), Date::STRING_FORMAT_YEAR)?></dd>
                <dt>End date:</dt>
                <dd><?=Tpl::moment(Date::getDateTime($this->subscription['endDate']), Date::STRING_FORMAT_YEAR)?></dd>
                <dt>Time remaining:</dt>
                <dd><?=Date::getRemainingTime(Date::getDateTime($this->subscription['endDate']))?></dd>

                <?php if(!empty($this->giftee)): ?>
                <dt>Gifted to:</dt>
                <dd><?=Tpl::out( $this->giftee['username'] )?></dd>
                <?php endif ?>

              </dl>
            </div>

            <input name="cancelSubscription" type="hidden" value="0" />
            <div class="g-recaptcha" data-sitekey="<?= Config::$a ['g-recaptcha'] ['key'] ?>"></div>

          </div>

          <div class="form-actions">
            <?php if($this->subscription['status'] == 'Active'): ?>
            <button type="button" id="cancelSubscriptionBtn" class="btn btn-danger">Cancel Subscription</button>
            <?php endif ?>
            <?php if($this->subscription['recurring'] == '1'): ?>
            <button type="button" id="stopRecurringBtn" class="btn btn-warning">Stop Recurring Payments</button>
            <?php endif ?>
            <a class="btn btn-link" href="/profile">Back to profile</a>
          </div>
        </form>
        <?php endif ?>

      </div>
    </section>
  </div>

  <?php include 'seg/foot.php' ?>
  <?php include 'seg/commonbottom.php' ?>
  <script src="<?=Config::cdnv()?>/web.js"></script>

  <script src="//www.google.com/recaptcha/api.js"></script>
  <script>
    (function(){
      $('button#cancelSubscriptionBtn').on('click', function(){
        $('input[name="cancelSubscription"]').val('1');
        $(this).closest('form').submit();
      });
      $('button#stopRecurringBtn').on('click', function(){
        $('input[name="cancelSubscription"]').val('0');
        $(this).closest('form').submit();
      });
    })();
  </script>

</body>
</html>