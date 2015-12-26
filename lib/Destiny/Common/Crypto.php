<?php
namespace Destiny\Common;
/*
 * This class attempts to follow the recommendations from
 * http://www.daemonology.net/blog/2009-06-11-cryptographic-right-answers.html
 * as close as possible, namely: use aes256 in ctr mode and use an hmac to
 * authenticate the payload, encrypted payloads are in the form of
 * |HMAC of IV and PAYLOAD|RANDOM IV|PAYLOAD|
 * since the HMAC and IV are both 32 bytes, the minimum length of a message is
 * 32byte HMAC + 32byte IV + payload + base64 overhead
 */
class Crypto {

    // initCrypt initializes the mcrypt module and generates
    // a random Initialization Vector if not provided
    static protected function initCrypt( $iv = null ) {
        $cryptmod = mcrypt_module_open( MCRYPT_RIJNDAEL_256, null, 'ctr', null );
        $keylen   = mcrypt_enc_get_key_size( $cryptmod );
        $seed     = Config::$a['crypto']['seed'];

        // when encrypting, caller should never provide an IV
        // always randomly generate a new IV for every message
        if ( !$iv )
            $iv  = mcrypt_create_iv( mcrypt_enc_get_iv_size( $cryptmod ), MCRYPT_DEV_URANDOM );

        // just to make sure the seed has enough length, maybe throw an exception
        // instead if the seed is not long enough?
        if ( strlen( $seed ) >= $keylen )
            $key = substr( $seed, 0, $keylen );
        else
            $key = str_pad( $seed, $keylen - strlen( $seed ) );

        mcrypt_generic_init( $cryptmod, $key, $iv );

        return array(
            'mod' => $cryptmod,
            'iv'  => $iv,
        );

    }

    // encrypt produces message in the form of:
    // |32bytes of hmac|32bytes of IV|encrypted payload|
    // we always generate a separate random IV for every single message
    static public function encrypt( $data ) {
        // initialize the mcrypt module with a random IV
        $crypt       = self::initCrypt();
        // encrypt the data
        $crypteddata = mcrypt_generic( $crypt['mod'], $data );
        // prepend the IV to the encrypted data
        $crypteddata = $crypt['iv'] . $crypteddata;
        // prepend the HMAC, protecting everything else
        $crypteddata =
            hash_hmac( 'sha256', $crypteddata, Config::$a['crypto']['seed'], true ) .
            $crypteddata
        ;

        // make it safe to use as a cookie
        $ret = base64_encode( $crypteddata );

        mcrypt_generic_deinit( $crypt['mod'] );
        return $ret;
    }

    static public function decrypt( $data ) {
        $crypteddata = base64_decode( $data );
        // HMAC is the first 32 bytes
        $givenhmac   = substr( $crypteddata, 0, 32 );
        // now we check that the payload actually matches the HMAC
        $crypteddata = substr( $crypteddata, 32 );
        $knownhmac   = hash_hmac( 'sha256', $crypteddata, Config::$a['crypto']['seed'], true );

        // compare the HMACs in a side-channel safe way
        if ( !self::constantTimeCompare( $knownhmac, $givenhmac ) )
            return '';

        // the IV is the next 32 bytes, the actual size of the IV is not really
        // important since we get it dynamically
        $ivlength    = mcrypt_get_iv_size( 'rijndael-256', 'ctr' );
        $iv          = substr( $crypteddata, 0, $ivlength );

        // everything else is the actual encrypted data
        $crypteddata = substr( $crypteddata, $ivlength );

        // pass in the IV
        $crypt       = self::initCrypt( $iv );
        $ret         = mdecrypt_generic( $crypt['mod'], $crypteddata );

        mcrypt_generic_deinit( $crypt['mod'] );
        return $ret;
    }

    static protected function constantTimeCompare($known, $given){
        if (strlen($known) == 0)
            throw new \InvalidArgumentException("This function cannot safely compare against an empty given string");

        $res  = strlen($given) ^ strlen($known);
        $glen = strlen($given);
        $klen = strlen($known);

        for ($i = 0; $i < $glen; ++$i)
            $res |= ord($known[$i % $klen]) ^ ord($given[$i]);

        return $res === 0;
    }

}