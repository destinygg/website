<?php
error_reporting(E_ALL);

$cssonly = ( @$argv[1] == 'css');
$html = file_get_contents('http://twitchemotes.com/');

$twitchdestination = 'img/img/twitch-%s.png';
@mkdir('img', 0775, true);
@mkdir('css', 0775, true);
preg_match_all('#<div class="span2">.*?<img src="([^"]*?)"/>.*>(.*?)</a></center><br/></div>#i', $html, $matches );

$customemotes = array('Draven', 'INFESTINY', 'FIDGETLOL', 'Hhhehhehe');
$triggers     = array();
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
	
}

foreach( $matches[1] as $key => $url ) {
	
	$trigger  = $matches[2][ $key ];
	preg_match('/-(\d+)x(\d+)\.png$/', $url, $dimensions );
	$filename = sprintf( $twitchdestination, $trigger );
	$path     = sprintf('twitch-%s.png', $trigger );
	
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
		$path
	);
	
}

file_put_contents('css/emoticons_unsprited.css', $css );
echo
	"/\\b(?:",
	implode("|", array_merge($customemotes, $matches[2])),
	")\\b/",
	"\n\nRun spritemapper css/emoticons_unsprited.css --anneal=100\n"
;
