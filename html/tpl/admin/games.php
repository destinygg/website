<?
namespace Destiny;
use Destiny\Utils\Date;
use Destiny\Utils\Lol;
?>
<ul class="nav nav-tabs" style="margin-bottom: 0; border-bottom: 0;">
	<li class="active"><a href="#Games" data-toggle="tab">Games</a></li>
	<li><a href="#Tracking" data-toggle="tab">Tracking</a></li>
</ul>
<div class="content content-dark clearfix"
	style="border-top-left-radius: 0; border-top-right-radius: 0;">
	<div style="width: 100%;" class="clearfix stream">
		<div class="tab-content">

			<div class="tab-pane active clearfix" id="Games">

				<table class="grid" style="width: 100%;">
					<thead>
						<tr>
							<td>Id</td>
							<td style="width: 100%">Type</td>
							<td>Start</td>
							<td>End</td>
							<td>Length</td>
							<td>Aggregated</td>
						</tr>
					</thead>
					<tbody>
						<?php foreach($model->games as $game):?>
						<?php
						$createdDate = new \DateTime ( $game ['gameCreatedDate'] );
						$endDate = new \DateTime ( $game ['gameEndDate'] );
						$length = round ( ($endDate->getTimestamp () - $createdDate->getTimestamp ()) / 60 );
						?>
						<tr>
							<td><?=$game['gameId']?></td>
							<td rel="<?=$game['gameType']?>">
								<?if($game['aggregated'] == '1'):?>
								<a rel="<?=$game['gameId']?>"
								class="btn btn-mini btn-warning btn-reset">Reset</a>
								<?endif;?>
								<?=Lol::$gameTypes[$game['gameType']]?>
							</td>
							<td><?=Date::getDateTime($game['gameCreatedDate'], 'H:i:s d-m-Y')?></td>
							<td><?=Date::getDateTime($game['gameEndDate'], 'H:i:s d-m-Y')?></td>
							<td><?=($length>0) ? $length .' minutes':'<span class="subtle">Unknown</span>'?></td>
							<td><?=($game['aggregated'] == '1')? Date::getDateTime($game['aggregatedDate'], Date::STRING_FORMAT):'False'?></td>
						</tr>
						<?endforeach;?>
						<?for($s=0;$s<10-count($model->games);$s++):?>
						<tr>
							<td>-</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
						</tr>
						<?endfor;?>
					</tbody>
				</table>

			</div>

			<div class="tab-pane clearfix" id="Tracking">

				<table class="grid" style="width: 100%;">
					<thead>
						<tr>
							<td>Id</td>
							<td style="width: 100%">Created</td>
							<td>Data</td>
						</tr>
					</thead>
					<tbody>
						<?foreach($model->tracks as $track):?>
						<tr>
							<td><?=$track['gameId']?></td>
							<td><?=Date::getDateTime($track['gameStartTime'], Date::STRING_FORMAT)?></td>
							<td><?=strlen($track['gameData'])?> <span class="subtle">bytes</span></td>
						</tr>
						<?endforeach;?>
						<?for($s=0;$s<10-count($model->tracks);$s++):?>
						<tr>
							<td>-</td>
							<td>-</td>
							<td>-</td>
						</tr>
						<?endfor;?>
					</tbody>
				</table>

			</div>

		</div>
	</div>
</div>