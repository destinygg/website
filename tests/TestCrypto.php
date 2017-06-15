<?php
use Destiny\Common\Crypto;
use Destiny\Common\Utils\Date;

class CryptoTest extends PHPUnit\Framework\TestCase {

    public function testCrypto() {
        $data = serialize(['userId'=>10, 'expiry'=>Date::getDateTime ( 'NOW' )->getTimestamp()]);
        $this->assertTrue($data === Crypto::decrypt(Crypto::encrypt($data)));
    }
}
