<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin" class="thin">

  <?php include Tpl::file('seg/top.php') ?>

  <section class="container">
    <ol class="breadcrumb" style="margin-bottom:0;">
      <li><a href="/admin">Users</a></li>
      <li class="active">Chat</li>
      <li><a href="/admin/subscribers">Subscribers</a></li>
      <li><a href="/admin/bans">Bans</a></li>
    </ol>
  </section>
				
  <?php if(!empty($model->error)): ?>
  <section class="container">
    <div class="alert alert-error" style="margin:0;">
      <strong>Error!</strong>
      <?=Tpl::out($model->error)?>
    </div>
  </section>
  <?php endif; ?>
	
  <?php if(!empty($model->success)): ?>
  <section class="container">
    <div class="alert alert-info" style="margin-bottom:0;">
      <strong>Success!</strong>
      <?=Tpl::out($model->success)?>
    </div>
  </section>
  <?php endif; ?>
	
  <section class="container">
    <h3>Broadcast</h3>
    <div class="content content-dark clearfix">
      <form class="form" action="/admin/chat/broadcast" role="form">
        <div class="ds-block">
          <div class="form-group">
            <label>Message:
            <br /><small>Send a broadcast message to the chat immediately</small>
            </label>
            <input name="message" type="text" class="form-control" value="" placeholder="Please no pasterino..." />
          </div>
          <button type="submit" class="btn btn-danger">Send</button>
        </div>
      </form>
    </div>
  </section>
	
  <section class="container">
    <h3>Search users by IP</h3>
    <div class="content content-dark clearfix">
      <form class="form-search" action="/admin/chat/ip">
        <div class="ds-block">
          <div class="form-group">
            <label>IP Address:
            <br /><small>Search for users by an IP address</small>
            </label>
            <input name="ip" type="text" class="form-control" value="<?=Tpl::out($model->searchIp)?>" placeholder="192.168.0.1" />
          </div>
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </form>
    </div>
  </section>
		
	<?php if(!empty($model->searchIp)): ?>
	<section class="container">
		<div class="content content-dark clearfix">
		<?php if(!empty($model->usersByIp)): ?>
  		<table class="grid">
  			<thead>
  				<tr>
  					<td>Username</td>
  					<td>Email</td>
  					<td>Created</td>
  				</tr>
  			</thead>
  			<tbody>
  			<?php foreach($model->usersByIp as $user): ?>
  				<tr>
  					<td><a href="/admin/user/<?=$user['userId']?>/edit"><?=Tpl::out($user['username'])?></a></td>
  					<td><?=Tpl::out($user['email'])?></td>
  					<td><?=Tpl::moment(Date::getDateTime($user['createdDate']), Date::STRING_FORMAT_YEAR)?></td>
  				</tr>
  			<?php endforeach; ?>
  			</tbody>
  		</table>
		<?php else: ?>
  		<div class="ds-block">
  			<p>No users with the IP "<?=Tpl::out($model->searchIp)?>"</p>
  		</div>
		<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>
	
	<?php include Tpl::file('seg/commonbottom.php') ?>
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>