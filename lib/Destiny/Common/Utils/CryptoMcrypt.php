<?php
namespace Destiny\Common\Utils;
/*
 * This class attempts to follow the recommendations from
 * http://www.daemonology.net/blog/2009-06-11-cryptographic-right-answers.html
 * as close as possible, namely: use aes256 in ctr mode and use an hmac to
 * authenticate the payload, encrypted payloads are in the form of
 * |PAYLOAD|RANDOM IV|HMAC of IV and PAYLOAD|
 * since the HMAC and IV are both 32 bytes, the minimum length of a message is
 * 32byte HMAC + 32byte IV + payload + base64 overhead
 * Note: the key and the seed MUST be different, if the plaintext for encrypted
 * data can be guessed, the key can be reversed but the seed for the hash will
 * still offer protection
 * THUS if the plaintext is easily guessable, one must assume the key is
 * compromised, so use accordingly and NEVER use without HMAC
 * TODO: use sha3 if available finally, make sure the hardcoded length of
 * the HMAC still matches
 */

use Destiny\Common\Config;
use Exception;
use InvalidArgumentException;

/**
 * @deprecated
 */
class CryptoMcrypt {

    /**
     * initCrypt initializes the mcrypt module and generates
     * a random Initialization Vector if not provided
     * @param null $iv
     * @return array
     * @throws Exception
     */
    static protected function initCrypt($iv = null ) {
        $cryptmod = mcrypt_module_open( MCRYPT_RIJNDAEL_256, null, 'ctr', null );
        $keylen   = mcrypt_enc_get_key_size( $cryptmod );
        $key      = Config::$a['crypto']['key'];

        // when encrypting, caller should never provide an IV
        // always randomly generate a new IV for every message
        if ( !$iv )
            $iv  = mcrypt_create_iv( mcrypt_enc_get_iv_size( $cryptmod ), MCRYPT_DEV_URANDOM );

        if ( strlen( $key ) < $keylen )
            throw new Exception("Config[crypto][key] is too short!");

        $key = substr( $key, 0, $keylen );
        mcrypt_generic_init( $cryptmod, $key, $iv );

        return [
            'mod' => $cryptmod,
            'iv'  => $iv,
        ];

    }

    /**
     * encrypt produces message in the form of:
     * |encrypted payload|32bytes of IV|32bytes of hmac|
     * we always generate a separate random IV for every single message
     * when providing the data, try to avoid using something easily guessable
     * so maybe include something random
     * @param $data
     * @return string
     * @throws Exception
     */
    static public function encrypt($data) {
        // initialize the mcrypt module with a random IV
        $crypt        = self::initCrypt();
        // encrypt the data
        $crypteddata  = mcrypt_generic( $crypt['mod'], $data );
        // append the IV to the encrypted data
        $crypteddata .= $crypt['iv'];
        // append the HMAC, protecting everything else
        $crypteddata .= hash_hmac( 'sha256', $crypteddata, Config::$a['crypto']['seed'], true );

        // make it safe to use as a cookie
        $ret = base64_encode( $crypteddata );

        mcrypt_generic_deinit( $crypt['mod'] );
        return $ret;
    }

    /**
     * @param $data
     * @return string
     * @throws Exception
     */
    static public function decrypt($data ) {
        $crypteddata = base64_decode( $data );
        // HMAC is the last 32 bytes
        $givenhmac   = substr( $crypteddata, -32 );
        // now we check that the payload actually matches the HMAC
        $crypteddata = substr( $crypteddata, 0, -32 ); // data without the HMAC
        $knownhmac   = hash_hmac( 'sha256', $crypteddata, Config::$a['crypto']['seed'], true );

        // compare the HMACs in a side-channel safe way
        if ( !self::constantTimeCompare( $knownhmac, $givenhmac ) )
            return '';

        // the IV is the next 32 bytes, the actual size of the IV is not really
        // important since we get it dynamically
        $ivlength    = mcrypt_get_iv_size( 'rijndael-256', 'ctr' );
        $iv          = substr( $crypteddata, -$ivlength );

        // everything else is the encrypted payload
        $crypteddata = substr( $crypteddata, 0, -$ivlength );

        // pass in the IV so that decryption can work
        $crypt       = self::initCrypt( $iv );
        $ret         = mdecrypt_generic( $crypt['mod'], $crypteddata );

        mcrypt_generic_deinit( $crypt['mod'] );
        return $ret;
    }

    static protected function constantTimeCompare($known, $given){
        if (strlen($known) == 0)
            throw new InvalidArgumentException("This function cannot safely compare against an empty given string");

        $res  = strlen($given) ^ strlen($known);
        $glen = strlen($given);
        $klen = strlen($known);

        for ($i = 0; $i < $glen; ++$i)
            $res |= ord($known[$i % $klen]) ^ ord($given[$i]);

        return $res === 0;
    }

}
