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
        $credentials = new SessionCredentials ( $user );
        $credentials->setAuthProvider ( 'API' );
        $credentials->addRoles ( UserRole::USER );
        $credentials->addFeatures ( UserFeaturesService::instance ()->getUserFeatures ( $authToken ['userId'] ) );
        $credentials->addRoles ( UserService::instance ()->getUserRolesByUserId ( $authToken ['userId'] ) );
        $subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $authToken ['userId'] );
        if (! empty ( $subscription )) {
            $credentials->addRoles ( UserRole::SUBSCRIBER );
            $credentials->addFeatures ( UserFeature::SUBSCRIBER );
            if ($subscription ['subscriptionTier'] == 2) {
                $credentials->addFeatures ( UserFeature::SUBSCRIBERT2 );
            }
            if ($subscription ['subscriptionTier'] == 3) {
                $credentials->addFeatures ( UserFeature::SUBSCRIBERT3 );
            }
        }
        $response = new Response ( Http::STATUS_OK, json_encode ( $credentials->getData () ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

}