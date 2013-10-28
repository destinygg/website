<?php
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="agreement">

	<?php include Tpl::file('seg/top.php') ?>
	
	<section class="container">
		<h1 class="title">
		<small class="subtle pull-right" style="font-size:14px; margin-top:20px;">Last update: <?=Date::getDateTime(filemtime(__FILE__))->format(Date::STRING_FORMAT)?></small>
		<span>Frequently Asked Questions</span>
		</h1>
		<hr size="1">
		
		<h3 id="refreshes">Why does the chat keep refreshing?</h3>
		<p>
			The chat backend is being updated, no need to fret.
		</p>
		
		<h3 id="highlight">What is this blue text?</h3>
		<p>
			So that is the color of the line when someone says your nick. It is called
			a highlight, and you can also go into the settings menu and enable
			<strong>desktop notifications</strong>.<br/>
			With that you now get notified when somebody says your nick and the
			browser is not in focus!<br/>
			Being able to disable any kind of highlighting is now completed, with
			the added ability of specifying custom words to highlight on.<br/>
			See the settings menu.
		</p>
		
		<h3 id="features">What are the features?</h3>
		<p>
			We aim to be mostly on-par with the twitch chat. Meaning that you should
			feel right at home. Just remember that this is mostly a bare essentials
			chat right now.<br/>
			We had twitch faces (they asked us to remove them, some have been recreated).<br/>
			Desktop notifications when someone highlights you (if your browser supports it).<br/>
			We reload the last N (currently 150, subject to change) lines of chat.<br/>
			There is a LOT of things on our TODO list, get in touch if you want to
			contribute (don't if you just want to ask for a specific feature)!
		</p>
		
		<h3 id="emotelist">Is there a list of emotes?</h3>
		<p>
			A very ghetto-one you can see with typing /emotes in chat, some of the twitch global emotes are supported.<br/>
			Proper list <a href="https://github.com/destinygg/website/wiki/Emotes">HERE</a><br/>
			There is <strong>tab auto-completion</strong> for emoticons, and don't forget:<br/>
			<strong>HOVER OVER THE EMOTICON to see how to produce it</strong>
		</p>
		
		<h3 id="flairs">How can I get my own flair? Is there a flair list?</h3>
		<p>
			There is a list, but it is a secret. Do something that is extraordinary
			and you might be rewarded with a <strong>flair of a lifetime</strong>.
		</p>
		
		<h3 id="bugs">Think you found a bug?</h3>
		<p>
			Are you sure?<br/>
			I mean sure, software has bugs no question about that, but can you
			reliably reproduce it? If yes, we want to hear from you!<br/>
			Go to the
			<a href="https://github.com/destinygg/website/issues">issue tracker</a>
			and create a ticket. Provide us with information, the more the merrier:<br/>
			first and foremost, show us how we can reproduce the issue
			(screen-shots, video, prose, whatever),<br/>
			provide the browser version, OS, and any other detail you think might be
			relevant.<br/>
			You get bonus points for a good bug report! The users with the most
			bug reports will receive their own flair (maybe)!
		</p>
		
		<h3 id="theme">I hate this dark theme! What do?</h3>
		<p>
			No theme support yet, and we are not convinced that it is worthwhile to
			implement it. (Will it look too out of place with the website theme?
			Can all of the features be represented in mostly the same way, like user
			colors? Who will take the time to thoroughly test that every theme works
			as intended after an update?)<br/>
			Convince us.
		</p>
		
		<h3 id="ratelimit">What is with this "Throttled" bullshit?</h3>
		<p>
			If you are sending messages too quickly you get more-and-more penalized.<br/>
			So the faster you send messages, the more you have to wait between
			those messages.<br/>
			TLDR: Use the chat as a normal person.
		</p>
		
		<h3 id="mutes">Got muted?</h3>
		<p>
			Mutes are ephemeral, never ever persistent. That said the user does not
			see the duration of the mute (deliberately, ask the mod to announce the duration).
			Mutes have a maximum duration of a week, and by default they are a minute long.<br/>
			There is no notification when the mute naturally expires (again, deliberately).<br/>
			TLDR: Don't worry, they are never persistent, it will pass.
		</p>
		
		<h3 id="bans">Where are the bans?</h3>
		<p>
			Bans are implemented on the chat back-end side, but lack the necessary
			GUI.<br/>
			The plan is to have a nice page where you can see why/when/for how long
			you were banned, and to easily appeal it (if you are a subscriber).
		</p>
		
		<h3 id="irc">How can I connect to the chat via IRC?</h3>
		<p>
			You can't, yet!
			The priority right now is to have a working and nice chat for the 90% and
			than cater later to that 10%.<br/>
			That said the code is open source and available at
			<a href="https://github.com/destinygg/chat">Github</a>,
			so hack it up and send a pull request (hit up sztanpet for direction).<br/>
			For now, DharmaTurtle is providing a service while "real" irc is implemented:<br/>
			chat is echoed to Rizon IRC at <a href="http://qchat.rizon.net/?channels=#destinyecho">#destinyecho</a>. Forwarding of IRC chat to DestinyChat is available (see the topic of the IRC channel for details).
		</p>
		
		<h3 id="changelog">Is there a changelog?</h3>
		<p>
			Yes!<br/>
			<a href="https://github.com/destinygg/website/commits/stable">For the website and chat front-end</a><br/>
			<a href="https://github.com/destinygg/chat/commits/master">For the chat back-end</a>
		</p>
		
		<h3 id="tabcomp">Tab completion please?</h3>
		<p>
			Now implemented and slick as fuck, don't forget to thank Ceneza!<br/>
			Also auto-completes emoticons! Try it: hhh+Tab
		</p>
	</section>
	
	<?php include Tpl::file('seg/panel.ads.php') ?>
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>
