<?
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
<body id="paymenterror">

	<?php include Tpl::file('seg/top.php') ?>
	
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
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
</body>
</html>