<?php
error_reporting(E_ALL);

define('_BASEDIR', realpath ( __DIR__ . '/../../' ) );

$config       = require _BASEDIR . '/config/config.php';
$customemotes = $config ['chat'] ['customemotes'];
$emotedir     = _BASEDIR . '/scripts/emotes/emoticons';
$outputdir    = _BASEDIR . '/scripts/emotes';

exec("glue --sprite-namespace= --namespace=chat-emote.chat-emote --optipng --crop $emotedir $outputdir", $output, $retcode );
if ( $retcode != 0 )
	die( implode("\n", $output ) );

$basecss  = file_get_contents( $outputdir . '/base.css' );
$emotecss = file_get_contents( $outputdir . '/emoticons.css' );

$emotecss = preg_replace('/height:(\d+px);/', 'height:$1;margin-top:-$1;', $emotecss );
file_put_contents( $outputdir . '/emoticons.css', $emotecss ); // for the preview

// update the frontend
$emotecss = str_replace("'emoticons.png'", "'../img/emoticons.png'", $emotecss );
file_put_contents( _BASEDIR . '/static/chat/css/emoticons.css', $basecss . $emotecss );
copy( $outputdir . '/emoticons.png', _BASEDIR . '/static/chat/img/emoticons.png' );

$triggers     = array();
$html  	      = array();

ob_start();
?>
<html>
<head>
<link href="base.css" rel="stylesheet" media="screen">
<link href="emoticons.css" rel="stylesheet" media="screen">
<style>
body {
	background-color: #000;
}

div.chat-emote {
	display: block;
	margin: 40px 0;
}
</style>

</head>
<body>
<br />
<br />
<?php foreach( $customemotes as $trigger ): ?>
	<div class="chat-emote chat-emote-<?=$trigger?>" title="<?=$trigger?>"></div>
<?php endforeach; ?>
</body>
</html>
<?php
file_put_contents( $outputdir . '/preview.html', ob_get_clean());
