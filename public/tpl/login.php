<?
namespace Destiny;
use Destiny\Utils\Http;
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
<body id="login">
	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
	
		<h1 class="title">
			<span>Login</span>
			<small>with your preferred login method</small>
		</h1>
		<hr size="1">
		
		<?php if(!empty($model->error)): ?>
		<div class="alert alert-error">
			<strong>Error!</strong>
			<?=Tpl::out($model->error->getMessage())?>
		</div>
		<?php endif; ?>
		
		<div class="content content-dark clearfix">
			<div class="control-group">
				<p>No private information will ever be shown on the website. This excludes the custom destiny.gg username you specify.</p>
				<span class="label label-inverse">Important!</span> Each login method will create a new user account <u>if they are not connected</u>.
				<br>To connect your accounts, use the method you first logged in with (twitch), and connect your other accounts within your profile.
			</div>
			<form id="loginForm" action="/login" method="post" style="margin:20px 0 0 0;">
			
				<div class="control-group">
					<div class="controls">
						<label class="checkbox">
							<input type="checkbox" name="rememberme" <?=($model->rememberme) ? 'checked':''?>> Remember my login
						</label>
						<span class="help-block">(this should only be used if you are on a private computer)</span>
					</div>
				</div>
				
				<div class="control-group">
					<label class="radio">
						<input type="radio" name="authProvider" value="twitch">
						<i class="icon-twitch"></i> Login with twitch
					</label>
				</div>
				<div class="control-group">
					<label class="radio">
						<input type="radio" name="authProvider" value="google">
						<i class="icon-google"></i> Login with google
					</label>
				</div>
				<div class="control-group">
					<label class="radio">
						<input type="radio" name="authProvider" value="twitter">
						<i class="icon-twitter"></i> Login with twitter
					</label>
				</div>
				
				<div class="form-actions" style="margin-bottom:0; border-radius:0 0 4px 4px;">
					<button type="submit" class="btn btn-primary">Login</button>
				</div>
			</form>
		</div>
		
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>