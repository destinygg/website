<?php
namespace Destiny\Common\Authentication;

interface AuthenticationHandler {

    function getAuthProviderId(): string;
    function getAuthorizationUrl($scope = [], $claims = ''): string;
    function getToken(array $params): array;
    function renewToken(string $refreshToken): array;
    function exchangeCode(array $params): OAuthResponse;

}