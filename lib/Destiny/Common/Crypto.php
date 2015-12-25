<?php
namespace Destiny\Common;

abstract class Crypto {

    static protected function initCrypt( $iv = null ) {
        // http://www.daemonology.net/blog/2009-06-11-cryptographic-right-answers.html
        $cryptmod = mcrypt_module_open( MCRYPT_RIJNDAEL_256, null, 'ctr', null );
        $keylen   = mcrypt_enc_get_key_size( $cryptmod );
        $seed     = Config::$a['crypto']['hashseed'];

        if ( !$iv )
            $iv  = mcrypt_create_iv( mcrypt_enc_get_iv_size( $cryptmod ), MCRYPT_DEV_URANDOM );

        // just to make sure the seed has enough length
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

    static public function encrypt( $data ) {
        $crypt       = self::initCrypt();
        $crypteddata = mcrypt_generic( $crypt['mod'], $data );
        $randomiv    = true;
        if ( $randomiv ) {
            $crypteddata = $crypt['iv'] . $crypteddata;
            $crypteddata =
                hash_hmac( 'sha256', $crypteddata, Config::$a['crypto']['hashseed'], true ) .
                $crypteddata
            ;
        }

        $ret = rawurlencode( base64_encode( $crypteddata ) );

        mcrypt_generic_deinit( $crypt['mod'] );
        return $ret;
    }

    static public function decrypt( $data ) {
        $crypteddata = base64_decode( rawurldecode( $data ) );
        $hmac        = substr( $crypteddata, 0, 32 );
        $crypteddata = substr( $crypteddata, 32 );
        $hmac2       = hash_hmac( 'sha256', $crypteddata, Config::$a['crypto']['hashseed'], true );

        if ( !self::constantTimeCompare( $hmac, $hmac2 ) )
            return '';

        $ivlength = mcrypt_get_iv_size( 'rijndael-256', 'ctr' );
        $iv          = substr( $crypteddata, 0, $ivlength );
        $crypteddata = substr( $crypteddata, $ivlength );

        $crypt       = self::initCrypt( $iv );
        $ret         = mdecrypt_generic( $crypt['mod'], $crypteddata );

        mcrypt_generic_deinit( $crypt['mod'] );
        return $ret;
    }

    static protected function constantTimeCompare($known_str, $given_str){
        if (strlen($known_str) == 0)
            throw new \InvalidArgumentException("This function cannot safely compare against an empty given string");

        $res = strlen($given_str) ^ strlen($known_str);
        $given_len = strlen($given_str);
        $known_len = strlen($known_str);

        for ($i = 0; $i < $given_len; ++$i) {
            $res |= ord($known_str[$i % $known_len]) ^ ord($given_str[$i]);
        }

        return $res === 0;
    }

}