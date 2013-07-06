<?
namespace Destiny;
use Destiny\Utils\Date;

use Destiny\Utils\Http;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/opengraph.php'?>
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="agreement">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
		<small class="subtle pull-right" style="font-size:14px; margin-top:20px;">Last update: <?=Date::getDateTime(filemtime(__FILE__))->format(Date::STRING_FORMAT)?></small>
		<span>Frequently Asked Questions</span>
		</h1>
		<hr size="1">
		
		<a name="highlight"></a>
		<h3>What is this yellow text?</h3>
		<p>
			So that is the color of the line when someone says your nick. It is called
			a highlight, and you can also go into the settings menu and enable
			desktop notifications.<br/>
			With that you now get notified when somebody says your nick and the
			browser is not in focus!<br/>
			Being able to disable any kind of highlighting is on the TODO
		</p>
		
		<a name="features"></a>
		<h3>What are the features?</h3>
		<p>
			We aim to be mostly on-par with the twitch chat. Meaning that you should
			feel right at home. Just remember that this is mostly a bare essentials
			chat right now.<br/>
			We have twitch faces (until they DMCA the fuck out of it at least).<br/>
			Desktop notifications when someone highlights you (if your browser supports it).<br/>
			Monospace font for all of your ASCII art needs (subject to change).<br/>
			We reload the last N (currently 150, subject to change) lines of chat.<br/>
			There is a LOT of things on our TODO list, get in touch if you want to
			contribute (don't if you just want to ask for a specific feature)!
		</p>
		
		<a name="emotelist"></a>
		<h3>Is there a list of emotes?</h3>
		<p>
			Not yet, twitch global emotes are all supported. We will have a list
			when there are actually some custom emotes.
		</p>
		
		<a name="flairs"></a>
		<h3>How can I get my own flair? Is there a flair list?</h3>
		<p>
			There is a list, but it is a secret. Do something that is extraordinary
			and you might be rewarded with a <strong>flair of a lifetime</strong>.
		</p>
		
		<a name="bugs"></a>
		<h3>Think you found a bug?</h3>
		<p>
			Are you sure?<br/>
			I mean sure, software has bugs no question about that, but can you
			reliably reproduce it? If yes, we want to hear from you!<br/>
			Go to the
			<a href="https://github.com/sztanpet/destinychat/issues">issue tracker</a>
			and create a ticket. Provide us with information, the more the merrier:<br/>
			first and foremost, show us how we can reproduce the issue
			(screen-shots, video, prose, whatever),<br/>
			provide the browser version, OS, and any other detail you think might be
			relevant.<br/>
			You get bonus points for a good bug report! The users with the most
			bug reports will receive their own flair (maybe)!
		</p>
		
		<a name="theme"></a>
		<h3>I hate this dark theme! What do?</h3>
		<p>
			No theme support yet, and we are not convinced that it is worthwhile to
			implement it. (Will it look too out of place with the website theme?
			Can all of the features be represented in mostly the same way, like user
			colors? Who will take the time to thoroughly test that every theme works
			as intended after an update?)<br/>
			Convince us.
		</p>
		
		<a name="ratelimit"></a>
		<h3>What is with this "Throttled" bullshit?</h3>
		<p>
			If you are sending messages too quickly you get more-and-more penalized.<br/>
			So the faster you send messages, the more you have to wait between
			those messages.<br/>
			TLDR: Use the chat as a normal person.
		</p>
		
		<a name="mutes"></a>
		<h3>Got muted?</h3>
		<p>
			Mutes are ephemeral, never ever persistent. That said the user does not
			see the duration of the mute (deliberately). Mutes have a maximum duration
			of a week, and by default they are an hour long.<br/>
			There is no notification when the mute naturally expires (again, deliberately).<br/>
			TLDR: Don't worry, they are never persistent, it will pass.
		</p>
		
		<a name="bans"></a>
		<h3>Where are the bans?</h3>
		<p>
			Bans are implemented on the chat back-end side, but lack the necessary
			GUI.<br/>
			The plan is to have a nice page where you can see why/when/for how long
			you were banned, and to easily appeal it (if you are a subscriber).
		</p>
		
		<a name="irc"></a>
		<h3>How can I connect to the chat via IRC?</h3>
		<p>
			You can't, yet!
			The priority right now is to have a working and nice chat for the 90% and
			than cater later to that 10%.<br/>
			That said the code is open source and available at
			<a href="https://github.com/sztanpet/destinychat">Github</a>,
			so hack it up and send a pull request (hit up sztanpet for direction).
		</p>
		
		<a name="changelog"></a>
		<h3>Is there a changelog?</h3>
		<p>
			For the back-end side, yes: on <a href="https://github.com/sztanpet/destinychat">Github</a><br/>
			For the front-end side: Soon&trade;
		</p>
	</section>
	
	<?include'./tpl/seg/panel.ads.php'?>
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
</body>
</html>