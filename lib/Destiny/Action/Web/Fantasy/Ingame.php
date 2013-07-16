<?php
namespace Destiny\Action\Web\Fantasy;

use Destiny\Common\Service\LeagueApiService;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Session;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Ingame {

	/**
	 * @Route ("/fantasy/ingame")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$app = Application::instance ();
		$cacheDriver = $app->getCacheDriver ();
		$ingame = null;
		foreach ( Config::$a ['lol'] ['summoners'] as $summoner ) {
			if ($summoner ['track'] == false) {
				continue;
			}
			$ingame = $cacheDriver->fetch ( 'ingame.' . $summoner ['id'] );
			if (! empty ( $ingame ) && ! empty ( $ingame ['gameData'] )) {
				// Abililty to send the game id, if it is still ingame, send a not modified response
				if (isset ( $params ['gameId'] ) && intval ( $params ['gameId'] ) == $ingame ['gameId']) {
					Http::status ( Http::STATUS_NOT_MODIFIED );
					Http::header ( Http::HEADER_CONNECTION, 'close' );
				}
				break;
			} else {
				$ingame = null;
			}
		}
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $ingame ) );
	}

}