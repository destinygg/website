<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="subscription" class="profile">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<h1 class="page-title">
			<span>Cancel</span>
			<small>subscription</small>
		</h1>
	</section>
		
	<section class="container">
		
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
			
				<?php if($model->subscriptionCancelled): ?>
				<div class="control-group">
					<p>
					<label class="label label-success">SUCCESS</label> Your subscription has been cancelled.
					Thank you for your support!
					</p>
				</div>
				<div class="control-group">
					<dl class="dl-horizontal">
						<dt>Status:</dt>
						<dd>
						<span class="label label-<?=($model->subscription['status'] == 'Active') ? 'success':'warning'?>"><?=Tpl::out($model->subscription['status'])?></span>
						<?php if($model->subscription['recurring']):?>
						<span class="label label-warning" title="This subscription is automatically renewed">Recurring</span>
						<?php endif; ?>
						</dd>
						
						<dt>Source:</dt>
						<dd><?=Tpl::out($model->subscription['subscriptionSource'])?></dd>
						<dt>Created date:</dt>
						<dd><?=Tpl::moment(Date::getDateTime($model->subscription['createdDate']), Date::STRING_FORMAT_YEAR)?></dd>
						<dt>End date:</dt>
						<dd><?=Tpl::moment(Date::getDateTime($model->subscription['endDate']), Date::STRING_FORMAT_YEAR)?></dd>
						<dt>Time remaining:</dt>
						<dd><?=Date::getRemainingTime(Date::getDateTime($model->subscription['endDate']))?></dd>
					</dl>
				</div>
				<div class="form-actions" style="margin:0;">
					<a class="btn" href="/profile">Back to profile</a>
				</div>
				<?php endif; ?>
			
				<?php if(!$model->subscriptionCancelled): ?>
				<form action="/subscription/cancel" method="post">
					<div class="control-group">
						<p>
						<label class="label label-important">WARNING</label> Cancelling an active subscription can only be undone by an administrator.
						<br>By clicking the 'Confirm Cancel' button you are confirming you want to cancel your active subscription immediately
						</p>
					</div>
					<div class="control-group">
						<dl class="dl-horizontal">
							<dt>Status:</dt>
							<dd>
							<span class="label label-<?=($model->subscription['status'] == 'Active') ? 'success':'warning'?>"><?=Tpl::out($model->subscription['status'])?></span>
							<?php if($model->subscription['recurring']):?>
							<span class="label label-warning" title="This subscription is automatically renewed">Recurring</span>
							<?php endif; ?>
							</dd>
							
							<dt>Source:</dt>
							<dd><?=Tpl::out($model->subscription['subscriptionSource'])?></dd>
							<dt>Created date:</dt>
							<dd><?=Tpl::moment(Date::getDateTime($model->subscription['createdDate']), Date::STRING_FORMAT_YEAR)?></dd>
							<dt>End date:</dt>
							<dd><?=Tpl::moment(Date::getDateTime($model->subscription['endDate']), Date::STRING_FORMAT_YEAR)?></dd>
							<dt>Time remaining:</dt>
							<dd><?=Date::getRemainingTime(Date::getDateTime($model->subscription['endDate']))?></dd>
						</dl>
					</div>
					<div class="form-actions" style="margin:0;">
						<button type="submit" class="btn btn-danger">Confirm cancel</button>
						<a class="btn" href="/profile">Back to profile</a>
					</div>
				</form>
				<?php endif; ?>
			</div>
		</div>
			
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>