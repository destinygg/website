<?php
namespace Destiny\Common;

interface AuthHandlerInterface {
    public function getAuthenticationUrl();
    public function authenticate(array $params);
}