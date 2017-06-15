<?php
namespace Destiny\Google;

use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Utils\Http;
use GuzzleHttp;

class GoogleRecaptchaHandler {

    /**
     * @param string $token The user response token provided by the reCAPTCHA to the user and provided to your site on.
     * @param Request $request The request
     * @return bool
     *
     * @throws Exception
     */
    public function resolve($token, Request $request){
        try {
            $client = new GuzzleHttp\Client(['timeout' => 20, 'connect_timeout' => 10, 'http_errors' => false]);
            $response = $client->get('https://www.google.com/recaptcha/api/siteverify', [
                'headers' => ['User-Agent' => Config::userAgent()],
                'query' => [
                    'response' => $token,
                    'remoteip' => $request->address(),
                    'secret' => Config::$a ['g-recaptcha'] ['secret']
                ]
            ]);
            if($response->getStatusCode() == Http::STATUS_OK){
                $data = GuzzleHttp\json_decode($response->getBody(), true);
                if(empty($data))
                    throw new Exception('Failed to resolve captcha.');
                if(!$data['success']){
                    if(isset($data['error-codes'])){
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
                    }else{
                        throw new Exception('Failed to resolve captcha.');
                    }
                }
            }
        } catch (\Exception $e) {
            $n = new Exception("Unknown error.", $e);
            Log::error($n);
            throw $n;
        }
        return true;
    }

}