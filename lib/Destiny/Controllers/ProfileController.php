<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Commerce\DonationService;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Application;
use Destiny\Common\Log;
use Destiny\Common\Utils\Date;
use Destiny\Common\Session;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Country;
use Destiny\Common\ViewModel;
use Destiny\Common\Request;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Api\ApiAuthenticationService;
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
     * @throws Exception
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
        return 'profile';
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
        $email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : $user ['email'];
        $country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : $user ['country'];
        $allowGifting = (isset ( $params ['allowGifting'] )) ? $params ['allowGifting'] : $user ['allowGifting'];

        try {
            $authService->validateUsername($username);
            if ($userService->getIsUsernameTaken($username, $user['userId'])) {
                throw new Exception ( 'The username you asked for is already being used' );
            }
            $authService->validateEmail($email, $user);
            if (! empty ( $country )) {
                $countryArr = Country::getCountryByCode ( $country );
                if (empty ( $countryArr )) {
                    throw new Exception ( 'Invalid country' );
                }
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
            'email' => $email,
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
        $model->title = 'Authentication';
        $model->user = $userService->getUserById($userId);

        // Build a list of profile types for UI purposes
        $authProfiles = $userService->getAuthByUserId($userId);
        $authProfileTypes = [];
        if (!empty ($authProfiles)) {
            foreach ($authProfiles as $profile) {
                $authProfileTypes [] = $profile ['authProvider'];
            }
            $model->authProfiles = $authProfiles;
        }
        $model->authProfileTypes = $authProfileTypes;

        $model->authTokens = ApiAuthenticationService::instance()->getAuthTokensByUserId($userId);
        $model->title = 'Authentication';
        return 'profile/authentication';
    }

    /**
     * @Route ("/profile/authtoken/create")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param Request $request
     * @return string
     *
     * @throws DBALException
     * @throws Exception
     */
    public function profileAuthTokenCreate(array $params, Request $request) {
        if (!isset($params['g-recaptcha-response']) || empty($params['g-recaptcha-response']))
            throw new Exception ('You must solve the recaptcha.');

        $googleRecaptchaHandler = new GoogleRecaptchaHandler();
        $googleRecaptchaHandler->resolve($params['g-recaptcha-response'], $request);

        $apiAuthService = ApiAuthenticationService::instance();
        $userId = Session::getCredentials()->getUserId();

        $user = UserService::instance()->getUserById($userId);
        $tokens = $apiAuthService->getAuthTokensByUserId($userId);
        if (count($tokens) >= 5) {
            throw new Exception ('You have reached the maximum [5] allowed login keys.');
        }

        $apiAuthService->createAuthToken($user);
        Session::setSuccessBag('Auth token created!');
        return 'redirect: /profile/authentication';
    }

    /**
     * @Route ("/profile/authtoken/{authToken}/delete")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     *
     * @throws DBALException
     * @throws Exception
     */
    public function profileAuthTokenDelete(array $params) {
        FilterParams::required($params, 'authToken');
        $userId = Session::getCredentials()->getUserId();
        $apiAuthService = ApiAuthenticationService::instance();
        $authToken = $apiAuthService->getAuthTokenById($params ['authToken']);
        if (empty ($authToken)) {
            throw new Exception ('Auth token not found');
        }
        if ($authToken ['userId'] != $userId) {
            throw new Exception ('Auth token not owned by user');
        }
        $apiAuthService->removeAuthToken($authToken ['authTokenId']);
        Session::setSuccessBag('Auth token removed!');
        return 'redirect: /profile/authentication';
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
    public function profileConnect(array $params) {
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
    public function profileRemove(array $params) {
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
            Session::setSuccessBag('Auth token removed');
            return 'redirect: /profile/authentication';
        }
        Session::setErrorBag('No auth profiles to remove.');
        return 'redirect: /profile/authentication';
    }

    /**
     * Discord update
     *
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
    public function updateDiscord(array $params){
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        FilterParams::declared ( $params, 'discordname' );
        $data = ['discordname' => $params['discordname']];

        if(trim($data['discordname']) == '')
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
     * @Route ("/profile/gifts")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     *
     * @param ViewModel $model
     * @return string
     * @throws DBALException
     * @throws Exception
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
     * Minecraft update
     *
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
    public function updateMinecraft(array $params){
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        FilterParams::declared ( $params, 'minecraftname' );
        $data = ['minecraftname' => $params['minecraftname']];
        if(trim($data['minecraftname']) == '')
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

}
