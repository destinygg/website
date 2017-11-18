<?php
namespace Destiny\Common\Utils;

abstract class Http {

    const HEADER_ETAG = 'Etag';
    const HEADER_STATUS = 'Status';
    const HEADER_CONTENTLENGTH = 'Content-Length';
    const HEADER_CONTENTTYPE = 'Content-Type';
    const HEADER_LAST_MODIFIED = 'Last-Modified';
    const HEADER_CACHE_CONTROL = 'Cache-Control';
    const HEADER_IF_MODIFIED_SINCE = 'If-Modified-Since';
    const HEADER_LOCATION = 'Location';
    const HEADER_PRAGMA = 'Pragma';
    const HEADER_CONNECTION = 'Connection';
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const HEADER_IF_NONE_MATCH = 'If-None-Match';
    const HEADER_REQUESTED_WITH = 'Requested-With';
    const HEADER_AUTH_TOKEN = 'AuthToken';

    const STATUS_MOVED_PERMANENTLY = 301;
    const STATUS_NOT_MODIFIED = 304;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_ERROR = 500;
    const STATUS_SERVICE_UNAVAILABLE = 503;
    const STATUS_OK = 200;
    const STATUS_NO_CONTENT = 204;
    
    public static $HEADER_STATUSES = [
        301 => 'Moved Permanently',
        500 => 'Error',
        404 => 'Not Found',
        401 => 'Unauthorized',
        400 => 'Bad Request',
        304 => 'Not Modified',
        200 => 'OK',
        204 => 'No Content',
        403 => 'Forbidden',
        503 => 'Service Unavailable'
    ];

    /**
     * @param string $name
     * @param string $value
     * @param bool $replace
     */
    public static function header($name, $value, $replace = true) {
        header ( $name . ': ' . $value, $replace );
    }

    /**
     * @param int $status
     */
    public static function status($status) {
        header ( 'HTTP/1.1 ' . $status . ' ' . self::$HEADER_STATUSES [intval ( $status )] );
    }
    
    /**
     * @return string
     */
    public static function getBaseUrl() {
        $protocol = 'http';
        if ($_SERVER ['SERVER_PORT'] == 443 || (! empty ( $_SERVER ['HTTPS'] ) && strtolower ( $_SERVER ['HTTPS'] ) == 'on')) {
            $protocol .= 's';
        }
        $host = $_SERVER ['HTTP_HOST'];
        $request = $_SERVER ['PHP_SELF'];
        return dirname ( $protocol . '://' . $host . $request );
    }

    /**
     * @param array $server
     * @return mixed|null
     */
    public static function extractIpAddress(array $server){
        $ip = isset($server ['REMOTE_ADDR']) ? $server ['REMOTE_ADDR'] : null;
        if (!empty ($server ['HTTP_X_REAL_IP'])) // ip from haproxy
            $ip = $server ['HTTP_X_REAL_IP'];
        else if (!empty ($server ['HTTP_CLIENT_IP'])) // check ip from share internet
            $ip = $server ['HTTP_CLIENT_IP'];
        else if (!empty ($server ['HTTP_X_FORWARDED_FOR'])) // to check ip is pass from proxy
            $ip = $server ['HTTP_X_FORWARDED_FOR'];
        return $ip;
    }

    /**
     * TODO if you need an arbitrary header, its not currently possible
     * @param array $server
     * @return array
     */
    public static function extractHeaders(array $server){
        $headers = [];
        if (isset ($server['HTTP_IF_NONE_MATCH']))
            $headers[self::HEADER_IF_NONE_MATCH] = $server['HTTP_IF_NONE_MATCH'];
        if (isset ($server['HTTP_IF_MODIFIED_SINCE']))
            $headers[self::HEADER_IF_MODIFIED_SINCE] = $server['HTTP_IF_MODIFIED_SINCE'];
        if (isset ($server['HTTP_X_REQUESTED_WITH']))
            $headers[self::HEADER_REQUESTED_WITH] = $server['HTTP_X_REQUESTED_WITH'];
        if (isset ($server['HTTP_X_AUTH_TOKEN']))
            $headers[self::HEADER_AUTH_TOKEN] = $server['HTTP_X_AUTH_TOKEN'];
        if (isset($server['HTTP_CONTENT_TYPE']))
            $headers[self::HEADER_CONTENT_TYPE] = $server['HTTP_CONTENT_TYPE'];
        return $headers;
    }

}