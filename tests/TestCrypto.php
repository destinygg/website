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
        $secret = hash("sha256", "Ql=QTP~-]Wr+A0>f_pkBHmWqHf+iO7=P/lUkq855P%W-QXS~Y<Zuv2g+wgi.wzhx");
        $codeVerifier = "1245j0f2093hf-0927h32f-0293";
        $codeChallenge = base64_encode(hash("sha256", $codeVerifier . $secret));
        echo "$codeChallenge";
    }

}
