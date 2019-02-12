<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Commerce\DonationService;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Authentication\OAuthService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Session\Session;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Country;
use Destiny\Common\Utils\RandomString;
use Destiny\Common\ViewModel;
use Destiny\Common\Request;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Discord\DiscordAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Common\Utils\FilterParams;
use Destiny\Google\GoogleRecaptchaHandler;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class ProfileController {

    /**
     * @Route ("/profile")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     *
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     */
    public function profile(ViewModel $model) {
        $userService = UserService::instance();
        $chatBanService = ChatBanService::instance();
        $subscriptionsService = SubscriptionsService::instance();
        $userId = Session::getCredentials()->getUserId();
        $model->credentials = Session::instance()->getCredentials()->getData();
        $model->ban = $chatBanService->getUserActiveBan($userId);
        $model->user = $userService->getUserById($userId);
        $model->gifts = $subscriptionsService->findByGifterIdAndStatus($userId, SubscriptionStatus::ACTIVE);
        $model->discordAuthProfile = $userService->getAuthByUserAndProvider($userId, 'discord');
        $model->subscriptions = $subscriptionsService->getUserActiveAndPendingSubscriptions($userId);
        $model->title = 'Account';
        return 'profile/account';
    }

    /**
     * @Route ("/profile/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function profileSave(array $params) {
        // Get user
        $userService = UserService::instance ();
        $authService = AuthenticationService::instance ();

        $userId = Session::getCredentials ()->getUserId();
        $user = $userService->getUserById($userId);

        if (empty ( $user )) {
            throw new Exception ( 'Invalid user' );
        }

        $username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : $user ['username'];
        $country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : $user ['country'];
        $allowGifting = (isset ( $params ['allowGifting'] )) ? $params ['allowGifting'] : $user ['allowGifting'];

        try {
            $authService->validateUsername($username);
            $userService->checkUsernameTaken($username, $user['userId']);
            if (! empty ( $country )) {
                $countryArr = Country::getCountryByCode ( $country );
                $country = $countryArr ['alpha-2'];
            }
        } catch ( Exception $e ) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /profile';
        }

        // Date for update
        $userData = [
            'username' => $username,
            'country' => $country,
            'allowGifting' => $allowGifting
        ];

        // Is the user changing their name?
        if (strcasecmp($username, $user ['username']) !== 0) {
            $nameChangedCount = intval($user ['nameChangedCount']);
            // have they hit their limit
            if ($nameChangedCount > 0) {
                $userData ['nameChangedDate'] = Date::getSqlDateTime();
                $userData ['nameChangedCount'] = $nameChangedCount - 1;
            } else {
                throw new Exception ('You have reached your name change limit');
            }
        }

        $userService->updateUser ( $user ['userId'], $userData );
        $authService->flagUserForUpdate ( $user ['userId'] );

        Session::setSuccessBag('Your profile has been updated' );
        return 'redirect: /profile';
    }

    /**
     * @Route("/profile/developer")
     * @Secure ({"USER"})
     *
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     */
    public function profileDeveloper(ViewModel $model) {
        $userId = Session::getCredentials()->getUserId();
        $oauthService = OAuthService::instance();
        $userService = UserService::instance();
        $model->title = 'Developer';
        $model->user = $userService->getUserById($userId);
        $model->oauthClients = $oauthService->getAuthClientsByUserId($userId);
        $model->accessTokens = $oauthService->getAccessTokensByUserId($userId);
        return 'profile/developer';
    }

    /**
     * @Route ("/profile/authentication")
     * @Secure ({"USER"})
     *
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     */
    public function profileAuthentication(ViewModel $model) {
        $userService = UserService::instance();
        $userId = Session::getCredentials()->getUserId();
        $authProfiles = $userService->getAuthByUserId($userId);
        $model->title = 'Authentication';
        $model->authProfileTypes = array_map(function($v){ return $v['authProvider']; }, $authProfiles);
        $model->authProfiles = $authProfiles;
        $model->user = $userService->getUserById($userId);
        return 'profile/authentication';
    }

    /**
     * @Route ("/profile/app/secret")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @ResponseBody
     *
     * @param array $params
     * @return array
     *
     * @throws DBALException
     */
    public function appSecretUpdate(array $params) {
        try {
            FilterParams::required($params, 'id');
            $userId = Session::getCredentials()->getUserId();
            $oauthService = OAuthService::instance();
            $client = $oauthService->getAuthClientById($params['id']);
            if (empty($client)) {
                throw new Exception('Invalid client_id');
            }
            if ($client['ownerId'] != $userId) {
                throw new Exception('You are not the owner of this client.');
            }
            $clientSecret = RandomString::make(64);
            $oauthService->updateAuthClient($params['id'], ['clientSecret' => hash('sha256', $clientSecret)]);
            return ['secret' => $clientSecret];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @Route ("/profile/app/create")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param Request $request
     * @return string
     * @throws DBALException
     */
    public function appCreate(array $params, Request $request) {
        try {
            FilterParams::required($params, 'name');
            FilterParams::required($params, 'redirectUrl');

            $userId = Session::getCredentials()->getUserId();
            $googleRecaptchaHandler = new GoogleRecaptchaHandler();
            $googleRecaptchaHandler->resolveWithRequest($request);
            $oauthService = OAuthService::instance();

            // Validate the application name
            $name = trim($params['name']);
            $this->validateAppName($name);

            // Validate redirectUrl
            $redirect = trim($params['redirectUrl']);
            $this->validateAppRedirect($redirect);

            // only allow 1 max application
            if (count($oauthService->getAuthClientsByUserId($userId)) >= 1) {
                throw new Exception ('You have reached the maximum [1] allowed applications.');
            }

            $oauthService->addAuthClient([
                'clientCode' => RandomString::makeUrlSafe(32),
                'clientSecret' => RandomString::make(64),
                'redirectUrl' => $redirect,
                'clientName' => $name,
                'ownerId' => $userId
            ]);
            Session::setSuccessBag('Application created!');
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
        }
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/app/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     *
     * @throws DBALException
     */
    public function appUpdate(array $params) {
        try {
            FilterParams::required($params, 'id');
            FilterParams::required($params, 'name');
            FilterParams::required($params, 'redirectUrl');

            $userId = Session::getCredentials()->getUserId();
            $oauthService = OAuthService::instance();
            $client = $oauthService->getAuthClientById($params['id']);

            if (empty($client)) {
                throw new Exception('Invalid client_id');
            }
            if ($client['ownerId'] != $userId) {
                throw new Exception('You are not the owner of this client.');
            }

            $name = trim($params['name']);
            $this->validateAppName($name);

            $redirect = trim($params['redirectUrl']);
            $this->validateAppRedirect($redirect);

            $oauthService->updateAuthClient($params['id'], ['clientName' => $name, 'redirectUrl' => $redirect]);
            Session::setSuccessBag('Application updated!');
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
        }
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/app/{id}/remove")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function appDelete(array $params) {
        FilterParams::required($params, 'id');
        $userId = Session::getCredentials()->getUserId();
        $oauthService = OAuthService::instance();
        $client = $oauthService->getAuthClientById($params['id']);
        if (empty($client)) {
            throw new Exception('Invalid client_id');
        }
        if ($client['ownerId'] != $userId) {
            throw new Exception('You are not the owner of this client.');
        }
        // Remove all associated access tokens
        $accessTokens = $oauthService->getAccessTokensByClientId($params['id']);
        foreach ($accessTokens as $token) {
            $oauthService->removeAccessToken($token['tokenId']);
        }
        // Remove the client
        $oauthService->removeAuthClient($params['id']);
        Session::setSuccessBag('Application removed');
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/authtoken/create")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param Request $request
     * @return string
     *
     * @throws DBALException
     */
    public function accessTokenCreate(Request $request) {
        try {
            $googleRecaptchaHandler = new GoogleRecaptchaHandler();
            $googleRecaptchaHandler->resolveWithRequest($request);

            $oauthService = OAuthService::instance();
            $userId = Session::getCredentials()->getUserId();

            // Users are only allowed 5 max login keys
            // We deem an access token a "login key" when it has no client
            // Typically it also is none expiring
            $accessTokens = $oauthService->getAccessTokensByUserId($userId);
            if (count(array_filter($accessTokens, function($v){ return $v['clientId'] == 0; })) >= 5) {
                throw new Exception ('You have reached the maximum [5] allowed login keys.');
            }

            $accessToken = RandomString::makeUrlSafe(64);
            $oauthService->addAccessToken([
                'clientId' => null,
                'userId' => $userId,
                'token' => $accessToken,
                'refresh' => null,
                'scope' => 'identify',
                'expireIn' => null,
            ]);
            Session::setSuccessBag('Login key created!');
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
        }
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/authtoken/{tokenId}/delete")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     *
     * @throws DBALException
     * @throws Exception
     */
    public function accessTokenDelete(array $params) {
        FilterParams::required($params, 'tokenId');
        $oauthService = OAuthService::instance();
        $userId = Session::getCredentials()->getUserId();
        $accessToken = $oauthService->getAccessTokenById($params['tokenId']);
        if (empty($accessToken)) {
            throw new Exception ('Invalid access token');
        }
        if ($accessToken['userId'] != $userId) {
            throw new Exception ('Access token not owned by user');
        }
        $oauthService->removeAccessToken($params['tokenId']);
        Session::setSuccessBag('Access token removed!');
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/connect/{provider}")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     */
    public function authProfileConnect(array $params) {
        FilterParams::required ( $params, 'provider' );
        $authProvider = $params ['provider'];

        // check if the auth provider you are trying to login with is not the same as the current
        $currentAuthProvider = Session::getCredentials ()->getAuthProvider ();
        if (strcasecmp ( $currentAuthProvider, $authProvider ) === 0) {
            throw new Exception ( 'Provider already authenticated' );
        }

        // Set a session var that is picked up in the AuthenticationService
        // in the GET method, this variable is unset
        Session::set ( 'accountMerge', '1' );

        switch (strtoupper ( $authProvider )) {
            case 'TWITCH' :
                $authHandler = new TwitchAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl ();

            case 'GOOGLE' :
                $authHandler = new GoogleAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl ();

            case 'TWITTER' :
                $authHandler = new TwitterAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl ();

            case 'REDDIT' :
                $authHandler = new RedditAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl ();

            case 'DISCORD' :
                $authHandler = new DiscordAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl ();

            default :
                throw new Exception ( 'Authentication type not supported' );
        }
    }

    /**
     * @Route ("/profile/remove/{provider}")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function authProfileRemove(array $params) {
        FilterParams::required($params, 'provider');
        $userId = Session::getCredentials()->getUserId();
        $userService = UserService::instance();
        $authProfile = $userService->getAuthByUserAndProvider($userId, $params ['provider']);
        if (empty($authProfile)) {
            Session::setErrorBag('Invalid provider');
            return 'redirect: /profile/authentication';
        }
        $authProfiles = $userService->getAuthByUserId($userId);
        if (!empty($authProfiles)) {
            $userService->removeAuthProfile($userId, $params ['provider']);
            Session::setSuccessBag('Login provider removed');
            return 'redirect: /profile/authentication';
        }
        Session::setErrorBag('No login provider to remove.');
        return 'redirect: /profile/authentication';
    }

    /**
     * @Route ("/profile/gifts")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     *
     * @param ViewModel $model
     * @return string
     * @throws DBALException
     */
    function gifts(ViewModel $model) {
        $userId = Session::getCredentials ()->getUserId ();
        $model->gifts = SubscriptionsService::instance()->findCompletedByGifterId($userId);
        $model->user = UserService::instance()->getUserById($userId);
        return 'profile/gifts';
    }

    /**
     * @Route ("/profile/donations")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     *
     * @param ViewModel $model
     * @return string
     * @throws DBALException
     */
    function donations(ViewModel $model) {
        $userId = Session::getCredentials ()->getUserId ();
        $model->donations = DonationService::instance()->findCompletedByUserId($userId);
        $model->user = UserService::instance()->getUserById($userId);
        return 'profile/donations';
    }

    /**
     * @Route ("/profile/subscriptions")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     *
     * @param ViewModel $model
     * @return string
     * @throws DBALException
     */
    function subscriptions(ViewModel $model) {
        $userId = Session::getCredentials ()->getUserId ();
        $model->subscriptions = SubscriptionsService::instance()->findCompletedByUserId($userId);
        $model->user = UserService::instance()->getUserById($userId);
        return 'profile/subscriptions';
    }

    /**
     * @Route ("/profile/discord/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function updateDiscord(array $params) {
        $userService = UserService::instance();
        $userId = Session::getCredentials()->getUserId();
        FilterParams::declared($params, 'discordname');
        $data = ['discordname' => $params['discordname']];
        if (trim($data['discordname']) == '')
            $data['discordname'] = null;
        if (mb_strlen($data['discordname']) > 36) {
            Session::setErrorBag('Discord username too long.');
            return 'redirect: /profile';
        }
        $uId = $userService->getUserIdByField('discordname', $params['discordname']);
        if ($data['discordname'] == null || empty($uId) || intval($uId) === intval($userId)) {
            $userService->updateUser($userId, $data);
            Session::setSuccessBag('Discord info has been updated');
        } else {
            Session::setErrorBag('Discord name already in use');
        }
        return 'redirect: /profile';
    }

    /**
     * @Route ("/profile/minecraft/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function updateMinecraft(array $params) {
        $userService = UserService::instance();
        $userId = Session::getCredentials()->getUserId();
        FilterParams::declared($params, 'minecraftname');
        $data = ['minecraftname' => $params['minecraftname']];
        if (trim($data['minecraftname']) == '')
            $data['minecraftname'] = null;
        if (mb_strlen($data['minecraftname']) > 16) {
            Session::setErrorBag('Minecraft name too long.');
            return 'redirect: /profile';
        }
        $uId = $userService->getUserIdByField('minecraftname', $params['minecraftname']);
        if ($data['minecraftname'] == null || empty($uId) || intval($uId) === intval($userId)) {
            $userService->updateUser($userId, $data);
            Session::setSuccessBag('Minecraft name has been updated');
        } else {
            Session::setErrorBag('Minecraft name already in use');
        }
        return 'redirect: /profile';
    }

    /**
     * @param string $name
     * @throws Exception
     */
    private function validateAppName($name) {
        if (preg_match('/^[A-Za-z0-9 ]{3,64}$/', $name) == 0) {
            throw new Exception ('Name may only contain A-z 0-9 or spaces and must be over 3 characters and under 64 characters in length.');
        }
    }

    /**
     * @param string $redirect
     * @throws Exception
     */
    private function validateAppRedirect($redirect) {
        if (mb_strlen($redirect) > 255) {
            throw new Exception ('Redirect URL has exceeded max length of 255 characters.');
        }
    }

}
