<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin">

	<?php include Tpl::file('seg/top.php') ?>
	
	<section class="container" id="admintabs">
		<h1 class="page-title">
			<span>User</span>
			<small>update</small>
		</h1>
		<div class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<form class="navbar-form" id="user-search">
					&nbsp;<input type="text" class="span2" value="<?=Tpl::out($model->user['username'])?>" placeholder="Enter a username..." autocomplete="off">
					<button type="submit" class="btn btn-inverse">Edit</button>
					<a href="/admin/" class="btn">Back</a>
				</form>
			</div>
		</div>
	</section>
	
	<section class="container" id="admintabs">
			
		<div>
			<h3>Ban / Mute</h3>
			<div class="content content-dark clearfix">
				<div class="clearfix">
					<?php 
					if(!empty($model->ban['id'])):
						$href='/admin/user/'. Tpl::out($model->user['userId']) .'/ban/'. Tpl::out($model->ban['id']) .'/update';
					else:
						$href='/admin/user/'. Tpl::out($model->user['userId']) .'/ban';
					endif; 
					?>
				
					<form action="<?=$href?>" method="post">
						
						<div class="control-group">
							<label class="control-label" for="inputUsername">Banned user</label>
							<div class="controls">
								<input type="text" readonly="readonly" class="uneditable-input" value="<?=Tpl::out($model->user['username'])?>">
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label" for="inputUsername">Reason</label>
							<div class="controls">
								<input type="text" name="reason" id="inputReason" value="<?=Tpl::out($model->ban['reason'])?>" placeholder="Reason">
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label" for="inputUsername">Start</label>
							<div class="controls">
								<input type="text" name="starttimestamp" id="inputStarttimestamp" value="<?=Tpl::out($model->ban['starttimestamp'])?>" placeholder="Y-m-d H:i:s">
								<span class="help-block">time specificed in UCT</span>
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label" for="inputUsername">End</label>
							<div class="controls">
								<input type="text" name="endtimestamp" id="inputEndtimestamp" value="<?=Tpl::out($model->ban['endtimestamp'])?>" placeholder="Y-m-d H:i:s">
								<span class="help-block">time specificed in UCT</span>
							</div>
						</div>
						
						<div class="form-actions" style="margin-bottom:0; border-radius:0 0 4px 4px;">
							<button type="submit" class="btn btn-primary">Save</button>
							<a href="/admin/user/<?=Tpl::out($model->user['userId'])?>/edit" class="btn">Back</a>
						</div>
						
					</form>
				</div>
			</div>
		</div>
		
	</section>
	<br>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>