<?php
namespace Destiny\Api;

use Destiny\Common\Response;
use Destiny\Common\MimeType;
use Destiny\Common\SessionCredentials;
use Destiny\Common\Utils\Http;
use Destiny\Common\Exception;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserFeaturesService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Authentication\AuthenticationService;

class ApiAuthHandler {
    
    /**
     * The current auth type
     *
     * @var string
     */
    protected $authProvider = 'API';

    /**
     * @param array $params
     * @throws Exception
     */
    public function authenticate(array $params) {
        if (! isset ( $params ['authtoken'] ) || empty ( $params ['authtoken'] )) {
            return new Response ( Http::STATUS_FORBIDDEN, 'Invalid or empty authToken' );
        }
        $authToken = ApiAuthenticationService::instance ()->getAuthToken ( $params ['authtoken'] );
        if (empty ( $authToken )) {
            return new Response ( Http::STATUS_FORBIDDEN, 'Auth token not found' );
        }
        $user = UserService::instance ()->getUserById ( $authToken ['userId'] );
        if (empty ( $user )) {
            return new Response ( Http::STATUS_FORBIDDEN, 'User not found' );
        }

        $authenticationService = AuthenticationService::instance ();
        $credentials = $authenticationService->getUserCredentials( $user, 'API');

        $response = new Response ( Http::STATUS_OK, json_encode ( $credentials->getData () ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

}