<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Exception;

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
    
    public static $HEADER_STATUSES = array (
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
    );

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
     * @throws Exception
     */
    public static function status($status) {
        if (! isset ( self::$HEADER_STATUSES [intval ( $status )] )) {
            throw new Exception ( sprintf ( 'HTTP status not supported %s', $status ) );
        }
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

}