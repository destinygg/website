<? 
namespace Destiny; 
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Config::$a['meta']['title']?></title>
<meta charset="utf-8">
<meta name="description" content="<?=Config::$a['meta']['description']?>">
<meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<link href="<?=Config::cdn()?>/css/vendor/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'seg/google.tracker.php'?>
</head>
<body id="subscribe">

	<?include'seg/top.php'?>
	
	<section class="container">
		<div class="content content-dark clearfix">
			<div style="width:100%;" class="clearfix stream">
				<h3 class="title"><span>Subscribe</span> <a href="#">destiny.gg</a></h3>
				<form action="/Order/Create" method="POST" style="margin:0; border-top:1px solid #222;">
					<fieldset>
						<div class="control-group" style="margin:10px 20px;">
							<p>Use your PayPal account to pay for the new subscriptions. <br />If you have an existing subscription, the expiration date will be extended by the new subscription.</p>
							<?$i=0;foreach(Config::$a['commerce']['subscriptions'] as $id=>$v):?>
							<label class="radio">
								<input type="radio" name="subscription" value="<?=$id?>"<?=($i == 0) ? ' checked':''?>>
								<span class="label label-inverse">$<?=number_format($v['amount'], 2)?></span> <?=Tpl::out($v['label'])?>
							</label>
							<?$i++;endforeach;?>
						</div>
						<div class="form-actions" style="margin:15px 0 0 0; border-top-left-radius:0; border-top-right-radius:0; border-bottom-right-radius:0;">
							<button type="submit" class="btn"><i class="icon-shopping-cart"></i> Proceed</button>
							<img src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
						</div>
					</fieldset>
				</form>
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