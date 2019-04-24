<?php
namespace Destiny\Discord;

use Destiny\Common\Config;
use Destiny\Common\Service;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * @method static DiscordMessenger instance()
 */
class DiscordMessenger extends Service {

    /**
     * @var Client
     */
    private $guzzle;

    /**
     * DiscordMessenger constructor.
     */
    public function __construct() {
        $this->guzzle = new Client([
            'timeout' => 10,
            'connect_timeout' => 10,
            'http_errors' => false
        ]);
    }

    /**
     * @param string $username
     * @param string $text
     * @param array $attachments
     * @return ResponseInterface
     */
    public function send($text, array $attachments = [], $username = '') {
        $webhook = Config::$a['discord']['webhook'] ?? '';
        if (!empty($webhook)) {
            $this->guzzle->post($webhook, [
                RequestOptions::JSON => [
                    'text' => $text,
                    'username' => empty($username) ? Config::$a['meta']['shortName'] : $username,
                    'attachments' => $attachments
                ],
            ]);
        }
        return null;
    }



}