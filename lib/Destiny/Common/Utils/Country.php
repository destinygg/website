<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Application;
use Destiny\Common\Config;

/**
 * This class is weird
 */
abstract class Country {
    
    /**
     * List of countries e.g.
     * [{"name":"Afghanistan","alpha-2":"AF","country-code":"004"},...] countries
     *
     * @var array
     */
    public static $countries = array ();
    
    /**
     * List of countries by code e.g.
     * {"AF":"Afghanistan"} countries
     *
     * @var array
     */
    public static $codeIndex = null;

    /**
     * Return a cached list of countries
     *
     * @return array
     */
    public static function getCountries() {
        if (self::$countries == null) {
            $cacheDriver = Application::instance ()->getCacheDriver ();
            $countries = $cacheDriver->fetch ( 'geodata' );
            if (empty ( $countries )) {
                $countries = json_decode ( file_get_contents ( _BASEDIR . '/config/countries.json' ), true );
                $cacheDriver->save ( 'geodata', $countries );
            }
            if (is_array ( $countries )) {
                self::$countries = $countries;
            }
        }
        if (empty ( self::$codeIndex )) {
            foreach ( self::$countries as $i => $country ) {
                self::$codeIndex [strtolower ( $country ['alpha-2'] )] = $i;
            }
        }
        return self::$countries;
    }

    public static function getCountryByCode($code) {
        $code = strtolower ( $code );
        $countries = self::getCountries ();
        return (isset ( self::$codeIndex [$code] )) ? $countries [self::$codeIndex [$code]] : null;
    }

}