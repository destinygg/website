<?php

use Destiny\Common\Utils\Crypto;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\RandomString;

class CryptoTest extends PHPUnit\Framework\TestCase {

    /**
     * @throws Exception
     */
    public function testCrypto() {
        $data = serialize(['userId' => 10, 'expiry' => Date::getDateTime('NOW')->getTimestamp()]);
        self::assertTrue($data === Crypto::decrypt(Crypto::encrypt($data)));
    }

    /**
     * @throws Exception
     */
    public function testSha() {
        $secret = hash("sha256", "vhmt5A0Nu=%I\$a_x/N-|NDY[=nyU3v3G~9JHBd]ljNqcE\$A>E2CN2We!MJki9m_>");
        $codeVerifier = RandomString::makeUrlSafe(42);
        $codeChallenge = base64_encode(hash("sha256", $codeVerifier . $secret));
        echo "Verifier: $codeVerifier" . PHP_EOL;
        echo "Challenge: $codeChallenge" . PHP_EOL;
    }

}
