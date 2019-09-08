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

    public static $alphabetFull = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-._~=$|![]#%@+<>/';
    public static $alphabetAlphaNumeric = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    public static $alphabetOnly = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public static function guid(int $length, string $alphabet): string {
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $alphabet[self::getRandomInteger(0, strlen($alphabet))];
        }
        return $token;
    }

    public static function make(int $length): string {
        return self::guid($length, self::$alphabetFull);
    }

    public static function makeUrlSafe(int $length): string {
        return self::guid($length, self::$alphabetAlphaNumeric);
    }

    protected static function getRandomInteger(int $min, int $max): int {
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