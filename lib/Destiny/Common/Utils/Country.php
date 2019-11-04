<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Doctrine\DBAL\DBALException;

/**
 * This class is weird
 */
abstract class Country {

    public static $countriesLoaded = false;

    /**
     * List of countries e.g.
     * [["AF" => "Afghanistan"], [ ... ] ...] countries
     *
     * @var array|null
     */
    public static $countries = [];
    /**
     * @var array|null
     */
    public static $codes = [];

    /**
     * Return a cached list of countries
     */
    public static function getCountries(): array {
        if (!self::$countriesLoaded) {
            self::$countriesLoaded = true;
            $cache = Application::getNsCache();
            $countries = $cache->fetch('countries');
            if (!$countries) {
                try {
                    self::$countries = self::fetchCountries();
                    self::$codes = array_column(self::$countries, 'code');
                    $cache->save('countries', self::$countries);
                } catch (\Exception $e) {
                    Log::error("Error loading countries. {$e->getMessage()}");
                }
            }
        }
        return self::$countries;
    }

    /**
     * @throws DBALException
     */
    protected static function fetchCountries(): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("SELECT * FROM countries ORDER BY FIELD(`code`, 'GB', 'US') DESC, label ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Return a country by code, if none exists throw an exception
     * @return array|null
     */
    public static function getCountryByCode(string $code) {
        $countries = self::getCountries();
        $index = array_search(strtoupper($code), self::$codes);
        return !$index ? null : $countries[$index];
    }

}