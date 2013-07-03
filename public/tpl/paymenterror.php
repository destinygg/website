<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="paymenterror">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Re-activate</span> <small>scheduled payment</small>
		</h1>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<div class="control-group">
					<p>An error has occurred during the activation process.
					<br>Please start again or email <a href="mailto:<?=Config::$a['paypal']['support_email']?>"><?=Config::$a['paypal']['support_email']?></a> for queries. </p>
					<div class="alert alert-error">
						<strong>Error!</strong>
						<?=Tpl::out($model->error->getMessage())?>
					</div>
				</div>
				<div class="form-actions block-foot">
					<img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
					<a href="/profile/subscription" class="btn">Back to profile</a>
				</div>
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>