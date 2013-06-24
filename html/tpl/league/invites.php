<?
namespace Destiny;
use Destiny\Utils\Date;
use Destiny\Utils\Http;
use Destiny\Utils\Lol;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<meta name="description" content="League of Legends Fantasy League. Free to play">
<meta name="keywords" content="League of Legends,Fantasy League,Free to play">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<meta property="og:site_name" content="<?=Config::$a['meta']['shortName']?>" />
<meta property="og:title" content="<?=Config::$a['meta']['shortName']?> : Fantasy League" />
<meta property="og:description" content="League of Legends Fantasy League. Free to play" />
<meta property="og:image" content="<?=Config::cdn()?>/img/destinyspash600x600.png" />
<meta property="og:url" content="<?=Http::getBaseUrl()?>" />
<meta property="og:type" content="video.other" />
<meta property="og:video" content="<?=Config::$a['meta']['video']?>" />
<meta property="og:video:secure_url" content="<?=Config::$a['meta']['videoSecureUrl']?>" />
<meta property="og:video:type" content="application/x-shockwave-flash" />
<meta property="og:video:height" content="259" />
<meta property="og:video:width" content="398" />
<link href="<?=Config::cdn()?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="invites" class="league">
	<?include'./tpl/seg/top.php'?>
	
	<?if(!Session::hasRole(\Destiny\UserRole::USER)):?>
	<?include'./tpl/seg/fantasy/calltoaction.php'?>
	<?endif;?>
	
	<?if(Session::hasRole(\Destiny\UserRole::USER)):?>
	<?include'./tpl/seg/fantasy/teambar.php'?>
	<?include'./tpl/seg/fantasy/teammaker.php'?>
	<?endif;?>
	
	<section class="container">
	
		<?php include'./tpl/seg/fantasy/fantasysubnav.php' ?>
		
		<div class="tab-content">
			<div id="Invites" class="tab-pane active clearfix">

				<div id="challengeInvites" class="content content-dark clearfix">
				
					<table class="grid pull-left">
						<thead>
							<tr>
								<td>Recieved</td>
							</tr>
						</thead>
						<tbody>
							<?foreach($model->invites as $invite):?>
							<?$title = Tpl::out($invite['username'])?>
							<tr>
								<td style="text-align: left;">
									<button class="btn btn-success btn-mini invite-accept" title="Accept invite" data-teamId="<?=$invite['teamId']?>">Accept</button>
									<button class="btn btn-danger btn-mini invite-decline" title="Decline invite" data-teamId="<?=$invite['teamId']?>">Decline</button>
									<?=Tpl::flag($invite['country'])?>
									<?=Tpl::subIcon($invite['subscriber'])?>
									<?=$title?>
								</td>
							</tr>
							<?endforeach;?>
							<?for($s=0;$s<1-count($model->invites);$s++):?>
							<tr>
								<td><span class="subtle">No challenges received.</span></td>
							</tr>
							<?endfor;?>
						</tbody>
					</table>
				
					<table class="grid pull-right">
						<thead>
							<tr>
								<td>Sent</td>
							</tr>
						</thead>
						<tbody>
							<?foreach($model->sentInvites as $sent):?>
							<?$title = Tpl::out($sent['username'])?>
							<tr>
								<td style="text-align: left;">
									<a href="#deletesentinvite" class="sent-invite-delete" title="Delete invite" data-teamId="<?=$sent['teamId']?>"><i class="icon-remove icon-white subtle"></i></a>
									<?=Tpl::flag($sent['country'])?>
									<?=Tpl::subIcon($sent['subscriber'])?>
									<?=$title?>
								</td>
							</tr>
							<?endforeach;?>
							<?for($s=0;$s<1-count($model->sentInvites);$s++):?>
							<tr>
								<td><span class="subtle">No challenges sent.</span></td>
							</tr>
							<?endfor;?>
						</tbody>
					</table>
				
					<form class="challengeForm clearfix pull-left">
						<div class="input-append">
							<input class="span3" autocomplete="off" name="name" type="text" placeholder="Who do you want to challenge?" />
							<button class="btn" type="submit">Challenge!</button>
						</div>
					</form>
				
				</div>
			
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/panel.ads.php'?>
	<?include'./tpl/seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>