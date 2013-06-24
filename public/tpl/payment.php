<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Currency;
use Destiny\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<meta name="description" content="<?=Config::$a['meta']['description']?>">
<meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<link href="<?=Config::cdn()?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="payment">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Payment</span> <small><?=Tpl::mask($model->payment['transactionId'])?></small>
		</h1>
		<div class="content content-dark clearfix">
			<div class="control-group clearfix">
				<dl class="dl-horizontal">
					<dt>Status:</dt>
					<dd><span class="label label-<?=($model->payment['paymentStatus'] == 'Completed') ? 'success':'warning'?>"><?=Tpl::out($model->payment['paymentStatus'])?></span></dd>
					<dt>Amount:</dt>
					<dd><?=Tpl::currency($model->payment['currency'], $model->payment['amount'])?></dd>
					<dt>Reference:</dt>
					<dd><?=Tpl::mask($model->payment['transactionId'])?></dd>
					<dt>Type:</dt>
					<dd><?=Tpl::out($model->payment['transactionType'])?></dd>
					<dt>Payer:</dt>
					<dd><?=Tpl::mask($model->payment['payerId'])?></dd>
					<dt>Payment:</dt>
					<dd><?=Tpl::out($model->payment['paymentType'])?></dd>
					<dt>Payed on:</dt>
					<dd><?=Date::getDateTime($model->payment['paymentDate'])->format(Date::STRING_FORMAT_YEAR)?></dd>
					<br>
					<dt title="This is the related order description">Description:</dt>
					<dd><?=Tpl::out($model->order['description'])?></dd>
					<dt>Order:</dt>
					<dd>#<?=Tpl::out($model->order['orderId'])?></dd>
					<dt>Recurring:</dt>
					<dd><?=Tpl::mask($model->paymentProfile['paymentProfileId'])?></dd>
				</dl>
			</div>
			<div class="form-actions block-foot">
				<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
				<a class="btn" href="/profile/subscription">Back to profile</a>
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>