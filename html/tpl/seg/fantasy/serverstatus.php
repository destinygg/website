<? namespace Destiny; ?>
<strong>Servers:</strong>
<?if(is_array($model->leagueServers)):?>
<?foreach ($model->leagueServers as $leagueServer):?>
<?if(in_array(strtolower($leagueServer['server']), Config::$a['lol']['trackedRegions'])):?>
<?if($leagueServer['status'] == 'OK'):?>
<span class="online"><?=$leagueServer['server']?></span>
<?endif;?>
<?if($leagueServer['status'] == 'OFFLINE'):?>
<span class="offline"><?=$leagueServer['server']?></span>
<?endif;?>
<?endif;?>
<?endforeach;?>
<?else:?>
<span class="offline">NONE</span>
<?endif;?>