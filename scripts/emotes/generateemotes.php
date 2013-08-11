<?php
error_reporting(E_ALL);

function genHTML($trigger) {
	global $html;
	$html[] = sprintf('<div class="chat-emote chat-emote-%1$s" title="%1$s"></div>', $trigger);
}

@mkdir('img', 0775, true);
@mkdir('css', 0775, true);

define('_BASEDIR', realpath ( __DIR__ . '/../../' ) );
chdir( _BASEDIR . '/scripts/emotes');

$config = require _BASEDIR . '/config/config.php';

$customemotes = $config ['chat'] ['customemotes'];
$twitchemotes = $config ['chat'] ['twitchemotes'];

$triggers     = array();
$html  	      = array();
$css          = '
/*
	spritemapper.output_css   = ../emoticons.css
	spritemapper.output_image = ../emoticons.png
	spritemapper.anneal_steps = 100
*/
.chat-emote {
	display: inline-block;
	position: relative;
	top: 10px;
	margin: 0 2px;
}
';
$emotecss = "
.chat-emote.chat-emote-%s {
	width: %dpx;
	height: %dpx;
	margin-top: -%dpx;
	background: url(../img/%s);
}
";

foreach( $customemotes as $trigger ) {
	
	$path       = $trigger . '.png';
	$filename   = 'img/' . $path;
	$dimensions = getimagesize( $filename );
	
	$css .= sprintf(
		$emotecss,
		$trigger,
		$dimensions[0],
		$dimensions[1],
		$dimensions[1],
		$path
	);
	genHTML($trigger);
}

foreach( $twitchemotes as $trigger ) {
	
	$path       = sprintf('twitch-%s.png', $trigger );
	$filename   = sprintf('img/twitch-%s.png', $trigger );
	$dimensions = getimagesize( $filename );
	
	$css .= sprintf(
		$emotecss,
		$trigger,
		$dimensions[0],
		$dimensions[1],
		$dimensions[1],
		$path
	);
	genHTML($trigger);
}

file_put_contents('css/emoticons_unsprited.css', $css );

ob_start();
?>
<html>
<head>
<link href="css/emoticons_unsprited.css" rel="stylesheet" media="screen">
<link href="emoticons.css" rel="stylesheet" media="screen">
<style>
body {
	background-color: #000;
}

div.chat-emote {
	display: block;
	margin: 50px 0;
}
</style>

</head>
<body>
<br />
<br />
<?=implode("\r\n", $html);?>
</body>
</html>
<?php
file_put_contents('preview.html', ob_get_clean());
echo
	"Run spritemapper css/emoticons_unsprited.css --anneal=100\n"
;

/*
	animation: rustle 100ms 7;
	-webkit-animation: rustle 100ms 7;
}
.chat-emote.chat-emote-OverRustle:hover {
	animation: rustle 100ms infinite;
	-webkit-animation: rustle 100ms infinite;
}
@keyframes rustle {
	from {
		left: 0px;
	}
	to {
		left: 2px;
	}
}
@-webkit-keyframes rustle {
	from {
		left: 0px;
	}
	to {
		left: 2px;
	}
*/
