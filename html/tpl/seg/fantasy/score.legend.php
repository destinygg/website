<?namespace Destiny;?>
<div class="content content-dark clearfix">
	<div class="stream clearfix" style="width: 33.33333%;">
		<h3 class="title" style="border-bottom: none;">
			Points <small class="subtle">(per game)</small>
		</h3>
		<ul style="padding: 10px 20px 20px; margin: 0; list-style: none;">
			<li><strong style="color: #1a6f00;"><?=Config::$a['fantasy']['scores']['PARTICIPATE']?></strong> point(s) are given for participation</li>
			<li><strong style="color: #1a6f00;"><?=Config::$a['fantasy']['scores']['WIN']?></strong> point(s) per champion on the winning team</li>
			<li>Receive <strong style="color: #b19e00;"><?=Config::$a['fantasy']['milestones'][0]['reward']['value']?></strong> transfer(s) every <strong><?=Config::$a['fantasy']['milestones'][0]['goalValue']?></strong> games</li>
			<li>Receive <strong style="color: #b19e00;"><?=Config::$a['fantasy']['credit']['scoreToCreditEarnRate']?></strong> credit(s) per point earned</li>
		</ul>
	</div>
	<div class="stream clearfix" style="width: 33.33333%;">
		<h3 class="title" style="border-bottom: none;">
			Limits <small class="subtle">(per team)</small>
		</h3>
		<ul style="padding: 10px 20px 20px; margin: 0; list-style: none;">
			<?if(Config::$a['fantasy']['team']['maxChampions'] != Config::$a['fantasy']['team']['minChampions']):?>
			<li><strong><?=Config::$a['fantasy']['team']['maxChampions']?></strong> maximum, <strong><?=Config::$a['fantasy']['team']['minChampions']?></strong> minimum champions</li>
			<?endif;?>
			<?if(Config::$a['fantasy']['team']['maxChampions'] == Config::$a['fantasy']['team']['minChampions']):?>
			<li><strong style="color: #b19e00;"><?=Config::$a['fantasy']['team']['minChampions']?></strong> champions required to make a team</li>
			<?endif;?>
			<li><strong style="color: #b19e00;"><?=Config::$a['fantasy']['team']['maxAvailableTransfers']?></strong>maximum available transfers</li>
			<li>Teams start with <strong style="color: #b19e00;"><?=Config::$a['fantasy']['team']['startCredit']?></strong> credit and <strong style="color: #b19e00;"><?=Config::$a['fantasy']['team']['startTransfers']?></strong> transfers</li>
		</ul>
	</div>
	<div class="stream clearfix" style="width: 33.33333%;">
		<h3 class="title" style="border-bottom: none;">Free champions</h3>
		<ul style="padding: 10px 20px 20px; margin: 0; list-style: none;">
			<li><strong style="color: #8a1919;">-<?=(Config::$a['fantasy']['team']['freeMultiplierPenalty']*100)?>%</strong> score penalty for free champion(s) points earned</li>
			<li>Free champions that are <strong>unlocked</strong> receive full points</li>
			<li>Free champions are rotated every 3 day(s)</li>
			<li>Champions that are <span style="text-decoration: underline;">not free</span> and <span style="text-decoration: underline;">not owned</span> do not earn points</li>
		</ul>
	</div>

	<hr size="1" style="width: 100%; visibility: hidden;" />

	<div class="stream clearfix" style="width: 33.33333%;">
		<h3 class="title" style="border-bottom: none;">Champion multipliers</h3>
		<p style="padding: 10px 20px 20px; margin: 0; color: #999;">
			Each champion has their own score multiplier. <br />Score * (1 - ((X/Y) * (Z/X))).<br /> X = Total games played by champion<br /> Y = Most played games by a single champion
			<br /> Z = Total games won by champion
		</p>
	</div>
	<div class="stream clearfix" style="width: 33.33333%;">
		<h3 class="title" style="border-bottom: none;">Games</h3>
		<ul style="padding: 10px 20px 20px; margin: 0; list-style: none;">
			<li>Games are automatically recorded, A delay up to 15 minutes can occur between each game</li>
			<li>Champions must be in the team at the time of the update to earn points</li>
			<li>Only champions which are picked <strong>before</strong> live games begin earn points
			</li>
		</ul>
	</div>
	<div class="stream clearfix" style="width: 33.33333%;">
		<h3 class="title" style="border-bottom: none;">Teammate bonus</h3>
		<p style="padding: 10px 20px 20px; margin: 0; color: #999;"> 
			Bonus points are given for each teammate.
			<br /> Score+round(Score *((a-1)/(<?=Config::$a['fantasy']['team']['maxPotentialChamps']?>-1))*<?=Config::$a['fantasy']['team']['teammateBonusModifier']?>).
		</p>
	</div>
</div>
