<?php
error_reporting(E_ALL);
$cssonly = ( @$argv[1] == 'css');
$html = file_get_contents('http://twitchemotes.com/');

$destination = 'img/twitch/%s.png';
preg_match_all('#<div class="span2">.*?<img src="([^"]*?)"/>.*>(.*?)</a></center><br/></div>#i', $html, $matches );

$triggers = array();
$css      = '
/*
	spritemapper.output_css = twitch_sprite.css
	spritemapper.anneal_steps = 100
*/
.twitch-emote {
	display: inline-block;
	position: relative;
	top: 10px;
}
';
$emotecss = "
.twitch-emote.twitch-emote-%s {
	width: %dpx;
	height: %dpx;
	margin: -%dpx 5px 0 5px;
	background: url(../img/twitch/%s.png);
}
";

foreach( $matches[1] as $key => $url ) {
	
	$trigger = $matches[2][ $key ];
	if (preg_match('/^\d/', $trigger)) // starts with digit -> invalid classname
		continue;
	
	$triggers[] = $trigger;
	preg_match('/-(\d+)x(\d+)\.png$/', $url, $dimensions );
	$filename = sprintf( $destination, $trigger );
	
	if ( !$cssonly ) {
		$img = file_get_contents( $url );
		file_put_contents( $filename, $img );
	}
	
	$css .= sprintf(
		$emotecss,
		$trigger,
		$dimensions[1],
		$dimensions[2],
		$dimensions[2],
		$trigger
	);
	
}

file_put_contents('css/twitch.css', $css );
echo implode("|", $triggers), "\n\nRun spritemapper css/twitch.css --anneal=100\n";
