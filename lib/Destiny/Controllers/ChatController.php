<?php
namespace Destiny\Controllers;

use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\Config;

/**
 * @Controller
 */
class ChatController {

    /**
     * @Route ("/chat/faq")
     *
     * @param ViewModel $model
     * @return string
     */
    public function faq(ViewModel $model) {
        $model->title = 'Frequently Asked Questions';
        return 'chat/faq';
    }
    
    /**
     * @Route ("/chat/history")
     */
    public function history() {
        $chatIntegrationService = ChatIntegrationService::instance();
        $lines = $chatIntegrationService->getChatLog();

        $out = 'var backlog = ' . json_encode ( $lines ) . ';' . PHP_EOL;

        $response = new Response ( Http::STATUS_OK, $out );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JAVASCRIPT );
        $response->addHeader ( Http::HEADER_CACHE_CONTROL, 'no-cache, max-age=0, must-revalidate, no-store' );
        return $response;
    }

    /**
     * @Route ("/chat/emotes.json")
     */
    public function emotes() {
        // just return every single one
        $emotes = array_merge(
            Config::$a ['chat'] ['customemotes'],
            Config::$a ['chat'] ['twitchemotes']
        );

        $response = new Response ( Http::STATUS_OK, json_encode ( $emotes ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

}
