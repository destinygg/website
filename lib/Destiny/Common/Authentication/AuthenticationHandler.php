<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Exception;

interface AuthenticationHandler {

    function getAuthProviderId(): string;
    function getAuthorizationUrl($scope = [], $claims = ''): string;

    function renewToken(string $refreshToken): array;

    /**
     * Exchange an OAuth code for a user access token.
     *
     * @throws Exception
     */
    function exchangeCode(array $params): OAuthResponse;

    /**
     * @throws Exception
     */
    function getToken(array $params): array;

}