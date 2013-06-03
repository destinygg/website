<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Date;
$subService = \Destiny\Service\Subscriptions::getInstance ();
$activeSub = $subService->getUserActiveSubscription(Session::$userId);
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
<body>

	<?include'seg/top.php'?>
	
	<section class="container">
		<div class="content content-dark clearfix">
		
			<div style="width:100%; margin-bottom:15px;" class="clearfix stream">
				<h3 class="title">Subscriptions</h3>
				<form action="/subscribe" method="post" style="margin:0; border-top:1px solid #222;">
					<fieldset>
						<div class="control-group" style="margin:10px 20px 20px 20px;">
						
							<?if(!empty($activeSub)):?>
							<p>Additional subscriptions can be purchased at any time.<br />Your active subscription will expire in <span class="label label-success"><?=Date::getRemainingTime(new \DateTime($activeSub['endDate']))?></span></p>
							<p><a href="/subscribe"><i class="icon-check icon-white"></i> Add subscription</a></p>
							<?else:?>
							<p>You have no active subscriptions. Additional subscriptions can be purchased at any time.</p>
							<p><a href="/subscribe"><i class="icon-check icon-white"></i> Subscribe</a></p>
							<?endif;?>
						
						</div>
					</fieldset>
				</form>
			</div>
		
			<div style="width:100%;" class="clearfix stream">
				<h3 class="title">Settings</h3>
				<form id="profileSaveForm" action="/ProfileSave" method="post" style="margin:0; border-top:1px solid #222;">
					<input type="hidden" name="url" value="/league" />
					<fieldset>
						<div class="control-group" style="margin:10px 20px;">
							<label>Country:</label>
							<select name="country">
								<option>Select your country</option>
								<?$countries = Utils\Country::getCountries();?>
								
								<option value="">&nbsp;</option>
								<option value="US" <?if(Session::$user['country'] == 'US'):?>selected="selected"<?endif;?>>United States</option>
								<option value="GB" <?if(Session::$user['country'] == 'GB'):?>selected="selected"<?endif;?>>United Kingdom</option>
								<option value="">&nbsp;</option>
								
								<?foreach($countries as $country):?>
								<option value="<?=$country->{'alpha-2'}?>" <?if(Session::$user['country'] != 'US' && Session::$user['country'] != 'GB' && Session::$user['country'] == $country->{'alpha-2'}):?>selected="selected"<?endif;?>><?=Tpl::out($country->name)?></option>
								<?endforeach;?>
							</select>
						</div>
						
						<div class="control-group" style="margin:10px 20px;">
							<label>Team Bar:</label>
							<label class="radio">
								<input type="radio" name="teambar_homepage" value="0" <?=(!Service\Settings::getInstance()->get('teambar_homepage')) ? 'checked':''?>>
								Show <u>only</u> on league page
							</label>
							<label class="radio">
								<input type="radio" name="teambar_homepage" value="1" <?=(Service\Settings::getInstance()->get('teambar_homepage')) ? 'checked':''?>>
								Show on home page &amp; league page
							</label>
						</div>
						
						<div class="form-actions" style="margin:15px 0 0 0; border-top-left-radius:0; border-top-right-radius:0; border-bottom-right-radius:0;">
							<button class="btn btn-primary" type="submit">Save changes</button>
							<button class="btn btn-danger" rel="resetteam">Reset team</button>
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