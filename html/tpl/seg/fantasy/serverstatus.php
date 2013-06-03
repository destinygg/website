<?
namespace Destiny;
use Destiny\Service\Leagueapi;
?>
<strong>Servers:</strong>
<?$leagueServers = Leagueapi::getInstance ()->getStatus (array('cacheFirst' => true))->getResponse()?>
<?if(is_array($leagueServers)):?>
<?foreach ($leagueServers as $leagueServer):?>
	<?if(in_array(strtolower($leagueServer->server), Config::$a['lol']['trackedRegions'])):?>
	<?if($leagueServer->status == 'OK'):?>
	<span class="online"><?=$leagueServer->server?></span>
	<?endif;?>
	<?if($leagueServer->status == 'OFFLINE'):?>
	<span class="offline"><?=$leagueServer->server?></span>
	<?endif;?>
	<?endif;?>
<?endforeach;?>
<?else:?>
<span class="offline">NONE</span>
<?endif;?>