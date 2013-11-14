<?
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
<style>
.games {
	padding: 15px;
}
.game.active label {
	color: white;
}
</style>
</head>
<body id="authentication" class="profile">

	<?php include Tpl::file('seg/top.php') ?>
	<?php if(empty($model->subscription)): ?>
	<?php include Tpl::file('seg/subscribebanner.php')?>
	<?php endif; ?>
	
	<section class="container">
		<div class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<ul class="nav pull-left">
					<li><a href="/profile" title="Your personal details">Details</a></li>
					<li class="active"><a href="/profile/games" title="Your games">Games</a></li>
				</ul>
				<ul class="nav pull-right">
					<li><a href="/profile/authentication" title="Your login methods">Authentication</a></li>
				</ul>
			</div>
		</div>
	</section>	
	
	<section class="container">
		<h3>Games</h3>
		<p style="color:#666;">Select the games you play or wish to play in the future</p>
		<div style="width: 100%;" class="clearfix stream">
			<div class="games content-dark clearfix">
				<?php foreach( $model->games as $game ):?>
				<div class="game<?=($game['active']) ? ' active':''?>">
					<label class="game-label checkbox">
						<input type="checkbox"<?=($game['active']) ? ' checked="checked"':''?> value="<?=Tpl::out($game['id'])?>" /> <?=Tpl::out($game['label'])?>
					</label>
				</div>
				<?php endforeach;?>
			</div>
		</div>
	</section>
	
	<br />
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
	<script>
	(function(){
		var addGame = function(gameId){
			$.ajax({
				url: '/profile/games/'+gameId+'/add',
				type: 'post'
			});
		};
		var removeGame = function(gameId){
			$.ajax({
				url: '/profile/games/'+gameId+'/remove',
				type: 'post'
			});
		};
		$('.games').on('change', 'input[type="checkbox"]', function(){
			if(!$(this).is(':checked')){
				removeGame($(this).val());
			}else{
				addGame($(this).val());
			}
			$(this).closest('.game').toggleClass('active');
		});
	})();
	</script>
	
</body>
</html>