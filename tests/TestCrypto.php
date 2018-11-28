<?php

use Destiny\Common\Crypto;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\RandomString;

class CryptoTest extends PHPUnit\Framework\TestCase {

    /**
     * @throws Exception
     */
    public function testCrypto() {
        $data = serialize(['userId' => 10, 'expiry' => Date::getDateTime('NOW')->getTimestamp()]);
        $this->assertTrue($data === Crypto::decrypt(Crypto::encrypt($data)));
    }

    /**
     * @throws Exception
     */
    public function testSha() {
        $secret = hash("sha256", "X8aZmv.q7K5.G4ctyJ5cX529uVGbyFYMQlJyY0Y~bTDraG.AM9BCitBgcYkJ7.l3");
        $codeVerifier = "1245j0f2093hf-0927h32f-0293";
        $codeChallenge = base64_encode(hash("sha256", $codeVerifier . $secret));
        echo "$codeChallenge";
    }

    /**
     * @throws Exception
     */
    public function testOther() {
        $key = RandomString::makeUrlSafe(32);
        echo $key . PHP_EOL;
        echo hash("sha256", $key, true) . PHP_EOL;
    }

    public function testRandom() {
        echo RandomString::makeUrlSafe(strlen('wuOWpRKjW2eRpRj8yuFNKKygaX'));
    }
}
