<?php
namespace Destiny\Discord;

use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\HttpClient;
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

    public function afterConstruct() {
        parent::afterConstruct();
        $this->guzzle = HttpClient::instance();
    }

    /**
     * @return ResponseInterface|null
     */
    public function send(string $text, array $attachments = [], string $username = '') {
        $webhook = Config::$a[AuthProvider::DISCORD]['webhook'] ?? '';
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