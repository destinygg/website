<?php

use Destiny\Common\Utils\Country;

class DataTests extends PHPUnit\Framework\TestCase {

    function testCountries() {
        $countries = Country::getCountries();
        $country = Country::getCountryByCode('US');
        $notCountry = Country::getCountryByCode('NONE');
        self::assertTrue(!empty($countries));
        self::assertTrue(!empty($country));
        self::assertTrue(empty($notCountry));
    }

}