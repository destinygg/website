<?php
namespace Destiny\Discord;

use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\HttpClient;
use Destiny\Common\Service;
use Destiny\Common\Utils\Http;
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

    public static function userLink(int $userId, string $username): string {
        return '<' . Http::getBaseUrl() . "/admin/user/{$userId}/edit|{$username}>";
    }

    /**
     * @return ResponseInterface|null
     */
    public static function send(string $text, array $attachment = []) {
        return self::instance()->sendMessage($text, [$attachment]);
    }

    /**
     * @return ResponseInterface|null
     */
    protected function sendMessage(string $text, array $attachments = []) {
        $webhook = Config::$a[AuthProvider::DISCORD]['webhook'] ?? '';
        if (!empty($webhook)) {
            $this->guzzle->post($webhook, [
                RequestOptions::JSON => [
                    'text' => $text,
                    'username' => empty($username) ? Config::$a['meta']['shortName'] : $username,
                    'attachments' => $attachments
                ],
            ]);
            // TODO error checking
        }
        return null;
    }

}