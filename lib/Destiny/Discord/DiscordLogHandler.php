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
            if (!empty($creds) && !empty($creds->getUserId()) && !empty($creds->getUsername())) {
                $fields[] = [
                    'title' => 'User',
                    'value' => DiscordMessenger::userLink($creds->getUserId(), $creds->getUsername()),
                    'short' => false
                ];
            }
            $fields[] = [
                'title' => 'Message',
                'value' => $record['message'],
                'short' => false
            ];
            DiscordMessenger::send('Error occurred.', [
                'color' => $color,
                'fields' => $fields
            ]);

        } catch (Exception $e) {
            // Recursion
            // Log::error("Error sending discord message." . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        return;
    }
}