<?
namespace Destiny;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="ordererror">

	<?include'./tpl/seg/top.php'?>
	
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
			<div style="width: 100%;" class="clearfix stream">
				<form action="/order/confirm" method="post">
					<input type="hidden" name="checkoutId" value="<?=$model->checkoutId?>" />
					<div class="control-group">
						<p>An error has occurred during the subscription process.
						<br>Please start again or email <a href="mailto:<?=Config::$a['paypal']['support_email']?>"><?=Config::$a['paypal']['support_email']?></a> for queries. </p>
						<div class="alert alert-error">
							<strong>Error!</strong>
							<?=Tpl::out($model->error->getMessage())?>
						</div>
					</div>
					<div class="form-actions block-foot">
						<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
						<a href="/subscribe" class="btn btn-primary"><i class="icon-chevron-left"></i> Subscriptions</a>
						<a href="/profile/subscription" class="btn">Back to profile</a>
					</div>
				</form>
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>