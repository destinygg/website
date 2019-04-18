<?php

use Destiny\Common\Utils\CryptoOpenSSL;
use Destiny\Common\Utils\Date;

class CryptoTest extends PHPUnit\Framework\TestCase {

    /**
     * @throws Exception
     */
    public function testCrypto() {
        $data = serialize(['userId' => 10, 'expiry' => Date::getDateTime('NOW')->getTimestamp()]);
        $encrypted = CryptoOpenSSL::encrypt($data);
        $decrypted = CryptoOpenSSL::decrypt($encrypted);
        echo PHP_EOL;
        echo "  Raw: $data" . PHP_EOL;
        echo "  Encrypted: " . serialize($encrypted) . PHP_EOL;
        echo "  Decrypted: $decrypted" . PHP_EOL;
        echo PHP_EOL;
        self::assertTrue($data === $decrypted);
    }

}
