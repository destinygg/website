<?php
error_reporting(E_ALL);
$cssonly = ( @$argv[1] == 'css');
$html = file_get_contents('http://twitchemotes.com/');

$destination = 'img/twitch/%s.png';
mkdir('img/twitch', 0775, true);
mkdir('css', 0775, true);
preg_match_all('#<div class="span2">.*?<img src="([^"]*?)"/>.*>(.*?)</a></center><br/></div>#i', $html, $matches );

$triggers = array();
$css      = '
/*
	spritemapper.output_css = emoticons.css
	spritemapper.anneal_steps = 100
*/
.twitch-emote {
	display: inline-block;
	position: relative;
	top: 10px;
	margin: 0 2px;
}
';
$emotecss = "
.twitch-emote.twitch-emote-%s {
	width: %dpx;
	height: %dpx;
	margin-top: -%dpx;
	background: url(../img/twitch/%s.png);
}
";

foreach( $matches[1] as $key => $url ) {
	
	$trigger = $matches[2][ $key ];
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
echo implode("|", $matches[2]), "\n\nRun spritemapper css/twitch.css --anneal=100\n";
