<?php
namespace Destiny\Google;

use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\HttpClient;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Utils\Http;
use function GuzzleHttp\json_decode;

class GoogleRecaptchaHandler {

    /**
     * @throws Exception
     */
    public function resolveWithRequest(Request $request, string $name = 'g-recaptcha-response'): bool {
        $value = $request->param($name);
        if (empty($value)) {
            throw new Exception ('You must solve the recaptcha.');
        }
        return $this->resolve($value, $request);
    }

    /**
     * @throws Exception
     */
    public function resolve(string $token, Request $request): bool {
        $response = HttpClient::instance()->get('https://www.google.com/recaptcha/api/siteverify', [
            'headers' => ['User-Agent' => Config::userAgent()],
            'query' => [
                'response' => $token,
                'remoteip' => $request->address(),
                'secret' => Config::$a ['g-recaptcha'] ['secret']
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $data = json_decode($response->getBody(), true);
            if (empty($data)) {
                throw new Exception('Failed to resolve captcha.');
            }
            if (!$data['success']) {
                if (isset($data['error-codes'])) {
                    switch ($data['error-codes']) {
                        case 'missing-input-secret':
                            throw new Exception('The secret parameter is missing.');
                        case 'invalid-input-secret':
                            throw new Exception('The secret parameter is invalid or malformed.');
                        case 'missing-input-response':
                            throw new Exception('The response parameter is missing.');
                        case 'invalid-input-response':
                            throw new Exception('The response parameter is invalid or malformed.');
                        default:
                            throw new Exception('Failed to resolve captcha.');
                    }
                } else {
                    throw new Exception('Failed to resolve captcha.');
                }
            }
            return true;
        }
        Log::error("Error resolving captcha none 200 result. {$response->getStatusCode()}");
        throw new Exception('Failed to resolve captcha.');
    }

}