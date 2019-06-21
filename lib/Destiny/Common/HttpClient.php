<?php
namespace Destiny\Common;

use GuzzleHttp\Client;

class HttpClient {

    public static function instance(array $conf = null): Client {
        return new Client(array_merge(Config::$a['curl'], $conf ?? []));
    }

}