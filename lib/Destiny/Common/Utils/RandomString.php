<?php
namespace Destiny\Common\Utils;

/**
 * Class RandomStringGenerator
 * @package Utils
 *
 * Solution taken from here:
 * http://stackoverflow.com/a/13733588/1056679
 */
class RandomString {

    /** @var string */
    protected static $alphabetFull = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-._~=$|![]#%@+<>/';

    /** @var string */
    protected static $alphabetAlphaNumeric = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * @param int $length
     * @param string $alphabet
     * @return string
     */
    private static function guid($length, $alphabet) {
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $alphabet[self::getRandomInteger(0, strlen($alphabet))];
        }
        return $token;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function make($length) {
        return self::guid($length, self::$alphabetFull);
    }

    /**
     * @param $length
     * @return string
     */
    public static function makeUrlSafe($length) {
        return self::guid($length, self::$alphabetAlphaNumeric);
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    protected static function getRandomInteger($min, $max) {
        $range = ($max - $min);
        if ($range < 0) {
            // Not so random...
            return $min;
        }
        $log = log($range, 2);
        // Length in bytes.
        $bytes = (int)($log / 8) + 1;
        // Length in bits.
        $bits = (int)$log + 1;
        // Set all lower bits to 1.
        $filter = (int)(1 << $bits) - 1;
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            // Discard irrelevant bits.
            $rnd = $rnd & $filter;
        } while ($rnd >= $range);
        return ($min + $rnd);
    }
}