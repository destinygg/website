<?
namespace Destiny;
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title>Ban Information</title>
<meta charset="utf-8">
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="banned">
	<?php include Tpl::file('seg/top.php') ?>

	<section class="container">
		<?php if( !isset( $model->user )):?>
			<h1 class="title">Not logged in</h1>
			<p>You need to be logged in to access this page!</p>
		<?php else:?>
			<?php if( empty( $model->ban )):?>
				<h1 class="title">No active bans found, congratulations!</h1>
				<p>Work harder!</p>
			<?php else:?>
				<h1 class="title">
					Dear <?php echo Tpl::out( $model->user['username'] ); ?>,
					you have been banned!
				</h1>
				<p>
					Note that any non-permanent bans are removed when subscribing as well
					as any mutes (there are no permanent mutes, maximum 6 days long).<br/>
					This is not meant to be
					a cash grab, rather a tool for those who would not like to wait for
					a manual unban or for the ban to naturally expire and are willing to
					pay for it.<br/>
					Feel free to evade the ban if you have da skillz.
				</p>
				<h2>Ban information:</h2>
				<table>
					<tr>
						<td>Time of the ban:</td>
						<td><?php echo $model->ban['starttimestamp']; ?> UTC</td>
					</tr>
					<tr>
						<td>End of the ban:</td>
						<td>
							<?php
								if ( $model->ban['endtimestamp'] )
									echo $model->ban['endtimestamp'], " UTC";
								else
									echo "It is permanent, sorry!";
							?>
						</td>
					</tr>
					<tr>
						<td>Reason:</td>
						<td><?php echo Tpl::out( $model->ban['reason'] ); ?></td>
					</tr>
				</table>
			<?php endif;?>
		<?php endif;?>
		
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>