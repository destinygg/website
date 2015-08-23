<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Transactional;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Common\ViewModel;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Api\ApiAuthHandler;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Common\Exception;

/**
 * @Controller
 */
class AuthenticationController {

    /**
     * @Route ("/api/auth")
     * @Transactional
     *
     * @param array $params
     * @return Response
     * @throws Exception
     */
    public function authApi(array $params) {
        try {
            $authHandler = new ApiAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            $response = new Response ( Http::STATUS_ERROR, $e->getMessage () );
            return $response;
        }
    }

    /**
     * @Route ("/auth/twitch")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function authTwitch(array $params, ViewModel $model) {
        try {
            $authHandler = new TwitchAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }

    /**
     * @Route ("/auth/twitter")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function authTwitter(array $params, ViewModel $model) {
        try {
            $authHandler = new TwitterAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }

    /**
     * @Route ("/auth/google")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function authGoogle(array $params, ViewModel $model) {
        try {
            $authHandler = new GoogleAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }

    /**
     * @Route ("/auth/reddit")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function authReddit(array $params, ViewModel $model) {
        try {
            $authHandler = new RedditAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }
}