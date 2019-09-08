<?php
namespace Destiny\Discord;

use Destiny\Common\Session\Session;
use Destiny\Common\Utils\Http;
use Exception;
use Monolog\Handler\AbstractProcessingHandler;

class DiscordLogHandler extends AbstractProcessingHandler {

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record) {
        try {
            // We may be running within the command line, no session object is instantiated
            $session = Session::instance();
            $creds = !empty($session) ? $session->getCredentials() : null;
            $username = !empty($creds) && $creds->isValid() ? "<". Http::getBaseUrl() ."/admin/user/{$creds->getUserId()}/edit|{$creds->getUsername()}>" : 'None';
            //
            $url = $_SERVER['REQUEST_URI'] ?? '';
            $color = $record['level'] >= 400 ? 'danger' : ($record['level'] >= 300 ? 'warning' : 'good');
            $fields = [];
            if (!empty($url)) {
                $fields[] = [
                    'title' => 'URL',
                    'value' => $url,
                    'short' => false
                ];
            }
            if (!empty($username)) {
                $fields[] = [
                    'title' => 'User',
                    'value' => $username,
                    'short' => false
                ];
            }
            $attachment = [
                'color' => $color,
                //'text' => $record['context']['trace'] ?? 'No stack trace.',
                'fields' => $fields
            ];

            $messenger = DiscordMessenger::instance();
            $messenger->send($record['message'], [$attachment]);

        } catch (Exception $e) {
            // Recursion
            // Log::error("Error sending discord message." . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        return;
    }
}