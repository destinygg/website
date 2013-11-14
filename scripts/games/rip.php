<?php
// Rips the games from twitch.tv API
// https://api.twitch.tv/kraken/games/top?limit={limit}

error_reporting(E_ALL ^ E_WARNING);
ini_set('display_errors', 1);

define ( '_BASEDIR', realpath ( __DIR__ . '/../../' ) );
define ( '_VENDORDIR', _BASEDIR . '/vendor' );
define ( '_STATICDIR', _BASEDIR . '/static' );

require _VENDORDIR . '/autoload.php';

use Destiny\Common\CurlBrowser;
use Destiny\Common\MimeType;
use Destiny\Common\Utils\String;

$stream = new \Monolog\Handler\StreamHandler ( 'php://stdout', \Monolog\Logger::DEBUG );
$stream->setFormatter ( new \Monolog\Formatter\LineFormatter ( "%level_name% %message%\n", "H:i:s" ) );
$log = new \Monolog\Logger ( 'DEBUG' );
$log->pushHandler ( $stream );

function getTwitchGames($log, $limit, $offset = 0) {
	$log->info ( sprintf ( 'Retrieving games...%s/%s', $offset, $offset + $limit ) );
	$ch = new CurlBrowser ( array (
			'logger' => $log,
			'verifyPeer' => false,
			'timeout' => 25,
			'url' => new String ( 'https://api.twitch.tv/kraken/games/top?limit={limit}&offset={offset}', array (
					'limit' => $limit,
					'offset' => $offset 
			) ),
			'contentType' => MimeType::JSON,
			'onfetch' => function ($json) {
				return $json;
			} 
	) );
	return $ch->getResponse ();
}

function saveRemoteImage($log, $url, $saveto) {
	$log->info ( sprintf ( 'Retrieving art...%s', $url ) );
	$ch = curl_init ( $url );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_BINARYTRANSFER, 1 );
	$raw = curl_exec ( $ch );
	$info = curl_getinfo ( $ch );
	$responseCode = intval ( $info ['http_code'] );
	curl_close ( $ch );
	if ($responseCode == 200) {
		if (file_exists ( $saveto ))
			unlink ( $saveto );
		file_put_contents($saveto, $raw);
		return true;
	}
	return false;
}

function getSafeName ($string) {
	$string = trim ( strtolower ( $string ) );
	$string = str_replace(array(' ', '\'', '"', '`', '.', '(', '[', ')', ']', ':'), array('-', '', '', '', '', '-', '-', '-', '-', '-'), $string);
	$string = preg_replace ( "/[^a-z0-9-]/", "-", $string );
	$string = preg_replace ( "/[-]+/", "-", $string );
	return trim ( $string, ' -' );
}

$games = array();
$data = getTwitchGames ( $log, 100 );
for($i = 0; $i < count ( $data ['top'] ); $i ++) {
	$game = new stdClass ();
	$game->label = $data ['top'] [$i] ['game'] ['name'];
	$game->name = getSafeName ( $game->label );
	$game->id = $data ['top'] [$i] ['game'] ['_id'];
	$games [] = $game;
	//saveRemoteImage ( $log, $data ['top'] [$i] ['game'] ['box'] ['small'], _BASEDIR . '/scripts/games/boxart/' . $game->name . '.' . pathinfo ( $data ['top'] [$i] ['game'] ['box'] ['small'], PATHINFO_EXTENSION ) );
}

file_put_contents(_BASEDIR . '/lib/Resources/games.json', json_encode ( $games ));
$log->info ( 'Complete' );
?>