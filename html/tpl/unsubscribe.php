<?

namespace Destiny;

use Destiny\Utils\Tpl;

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
<?include'seg/google.tracker.php'?>
</head>
<body id="unsubscribe">

	<?include'seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Unsubscribe</span> <small>we're sorry to see you go</small>
		</h1>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
			
				<?php if(!$model->unsubscribed): ?>
				<form action="/unsubscribe" method="post" style="margin: 0;">
					<input type="hidden" name="confirmationId" value="<?=$model->confirmationId?>" />
					<fieldset>
						<div class="control-group" style="margin: 10px 20px;">
							<p>
								<span class="label label-inverse">WARNING</span> You are about
								to cancel your subscription, this cannot be reversed. <br />You
								can create a new subscription at any time.
							</p>
						</div>
						<div class="form-actions" style="margin: 15px 0 0 0; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0;">
							<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
							<button type="submit" class="btn btn-danger"><i class="icon-remove icon-white"></i> I want to cancel my subscription</button>
							<a href="/profile" class="btn"><i class="icon-user"></i> Go back to profile</a>
						</div>
					</fieldset>
				</form>
				<?php endif; ?>
				
				<?php if($model->unsubscribed): ?>
				<form style="margin: 0;">
					<fieldset>
						<div class="control-group" style="margin: 10px 20px;">
							<p>
								<span class="label label-inverse">Cancelled</span> Your
								subscription has been cancelled.<br> Thank you for your support!
							</p>
						</div>
						<div class="form-actions" style="margin: 15px 0 0 0; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0;">
							<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
								<a href="/profile" class="btn"><i class="icon-user"></i> Go back to profile</a>
						</div>
					</fieldset>
				</form>
				<?php endif; ?>
				
			</div>
		</div>
	</section>
	
	<?include'seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>