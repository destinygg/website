<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Lol;

$champions = Service\Fantasy\Cache::getInstance()->getChampions ();
$championNames = array ();
foreach ( $champions as $champ ) {
	$championNames [] = $champ->championName;
}
?>
<div class="team-maker-overlay"></div>
<div class="team-maker" data-settings="<?=Tpl::out(json_encode(Config::$a['fantasy']['team']))?>" style="display: none;">
	<div class="clearfix champ-filters">
		<div class="champ-search pull-left">
			<input placeholder="Search champions..." value="" class="input-xlarge" type="text" data-source="<?=Tpl::out(json_encode($championNames))?>" />
		</div>
		<div class="team-maker-console pull-right"></div>
	</div>
	<div class="clearfix champ-sorting">
		<div class="btn-group clearfix pull-right" style="margin-left:10px;">
			<a class="btn btn-mini btn-inverse dropdown-toggle" data-toggle="dropdown">
				Sort by
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu">
				<li><a data-by="name">Label</a></li>
				<li><a data-by="value">Price</a></li>
			</ul>
		</div>
		<div class="btn-group clearfix pull-right">
			<button class="btn btn-mini btn-inverse pull-left champ-free-filter" data-toggle="buttons-radio">
				Free champions only
			</button>
		</div>
	</div>
	<div class="team-maker-selection" id="champSelector">
		<div class="team-maker-selection-inner clearfix"></div>
	</div>
	
	<div class="team-maker-currency" style="text-align: center;">
		<span title="Available credits"><i class="icon-money"></i><strong class="credits">0</strong> <span>Credits</span></span><span title="Available transfers"><i class="icon-transfers"></i><strong class="transfers">0</strong> <span>Transfers</span></span>
	</div>
	
	<div class="team-maker-slots">
		<div class="clearfix">
		<?for ($i=0;$i<Config::$a['fantasy']['team']['maxChampions']; $i++):?>
			<div class="champion-slot champion-slot-empty" style="width:<?=(100/Config::$a['fantasy']['team']['maxChampions'])?>%; float:left;">
				<div class="thumbnail" style="border:none;"><img src="<?=Config::cdn()?>/img/320x320.gif" /></div>
			</div>
		<?endfor;?>
		</div>
	</div>
	
	<div class="team-maker-help">
		<p>
			To <strong>remove</strong> a champion from your team, click on the portrait in the team bar above.<br />
			To <strong>add</strong> a champion to your team, click on the portait in the champion grid above the team bar.
		</p>
	</div>
	
	<div class="team-maker-tools">
		<button class="btn-confirm btn btn-primary" title="Confirm update"><i class="icon-ok icon-white"></i> Update</button>
		<button class="btn-close btn btn-inverse" title="Close window"><i class="icon-remove icon-white"></i> Close</button>
	</div>
	
	<div class="team-maker-inner-overlay" style="display: none;"></div>
	<div class="team-maker-progress" style="display: none;"></div>
	
</div>
