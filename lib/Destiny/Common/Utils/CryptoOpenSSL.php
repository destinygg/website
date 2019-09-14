<?php
namespace Destiny\Common\Utils;
/*
 * This class attempts to follow the recommendations from
 * http://www.daemonology.net/blog/2009-06-11-cryptographic-right-answers.html
 * as close as possible, namely: use aes256 in ctr mode and use an hmac to
 * authenticate the payload, encrypted payloads are in the form of
 * |PAYLOAD|RANDOM IV|HMAC of IV and PAYLOAD|
 * The minimum length of a message is
 *   64byte HMAC + 16byte IV + payload + base64 overhead
 * Note: the key and the seed MUST be different, if the plaintext for encrypted
 * data can be guessed, the key can be reversed but the seed for the hash will
 * still offer protection
 * THUS if the plaintext is easily guessable, one must assume the key is
 * compromised, so use accordingly and NEVER use without HMAC
 *
 * Class originally written by sztanpet and upgraded to use OpenSSL by someone less talented.
 */

use Destiny\Common\Config;

class CryptoOpenSSL {

    // IV 16byte (128bit), Key 32byte (256bit) in CTR (Counter) mode
    const CIPHER_ALGORITHM = 'aes-256-ctr';
    const HMAC_ALGORITHM = 'sha512';
    const HMAC_LENGTH = 64;

    /**
     * encrypt produces message in the form of:
     * |encrypted payload|32bytes of IV|32bytes of hmac|
     * we always generate a separate random IV for every single message
     * when providing the data, try to avoid using something easily guessable
     * so maybe include something random
     */
    static public function encrypt(string $data): string {
        $seed = Config::$a['crypto']['seed'];
        $key = Config::$a['crypto']['key'];
        // initialize the random IV
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_ALGORITHM));
        // encrypt the data
        $crypteddata = openssl_encrypt($data, self::CIPHER_ALGORITHM, $key, OPENSSL_RAW_DATA, $iv);
        // append the IV to the encrypted data
        $crypteddata .= $iv;
        // append the HMAC, protecting everything else
        $crypteddata .= hash_hmac(self::HMAC_ALGORITHM, $crypteddata, $seed, true);
        // return base64
        return base64_encode($crypteddata);
    }

    static public function decrypt(string $data): string {
        $seed = Config::$a['crypto']['seed'];
        $key = Config::$a['crypto']['key'];
        // base64 decode
        $crypteddata = base64_decode($data);
        // HMAC is the last 32 bytes
        $givenhmac = substr($crypteddata, -self::HMAC_LENGTH);
        // now we check that the payload actually matches the HMAC
        $crypteddata = substr($crypteddata, 0, -self::HMAC_LENGTH); // data without the HMAC
        $knownhmac = hash_hmac(self::HMAC_ALGORITHM, $crypteddata, $seed, true);

        // compare the HMACs in a side-channel safe way
        if (!self::constantTimeCompare($knownhmac, $givenhmac)) {
            return '';
        }

        // the IV is the next 32 bytes, the actual size of the IV is not really
        // important since we get it dynamically
        $ivlength = openssl_cipher_iv_length(self::CIPHER_ALGORITHM);
        $iv = substr($crypteddata, -$ivlength);

        // everything else is the encrypted payload
        $crypteddata = substr($crypteddata, 0, -$ivlength);

        // pass in the IV so that decryption can work
        return openssl_decrypt($crypteddata, self::CIPHER_ALGORITHM, $key, OPENSSL_RAW_DATA, $iv);
    }

    static protected function constantTimeCompare(string $known = null, string $given = null): bool {
        if (strlen($known) == 0) {
            return false;
        }
        $res = strlen($given) ^ strlen($known);
        $glen = strlen($given);
        $klen = strlen($known);
        for ($i = 0; $i < $glen; ++$i) {
            $res |= ord($known[$i % $klen]) ^ ord($given[$i]);
        }
        return $res === 0;
    }

}
