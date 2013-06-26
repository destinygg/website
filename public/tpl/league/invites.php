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
<?include'./tpl/seg/opengraph.php'?>
<?include'./tpl/seg/commontop.php'?>
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
	<?include'./tpl/seg/commonbottom.php'?>
</body>
</html>