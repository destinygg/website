<?php

use Destiny\StreamLabs\StreamLabsService;
use Destiny\StreamLabs\StreamLabsAlertsType;

class TwitchAlertTest extends PHPUnit\Framework\TestCase {

    private function getService(): StreamLabsService {
        return StreamLabsService::instance();
    }

    /**
     * @throws Exception
     */
    public function testOne() {
        $r = $this->getService()->sendAlert([
            'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
            'message' => '*Billy* bought a *Catarrian Shirt*!'
        ]);
        print_r(json_decode($r->getBody(), true));
        self::assertArrayHasKey('success', json_decode($r->getBody(), true));
    }

    /**
     * @throws Exception
     */
    public function testTwo() {
        $r = $this->getService()->sendAlert([
            'type' => StreamLabsAlertsType::ALERT_DONATION,
            'message' => sprintf("*%s* has donated *%s*! %s", 'Billy', '$' . number_format(32, 2), '')
        ]);
        print_r(json_decode($r->getBody(), true));
        self::assertArrayHasKey('success', json_decode($r->getBody(), true));
    }

    /**
     * @throws Exception
     */
    public function testThree() {
        $r = $this->getService()->sendDonation([
            'name' => 'PersonX',
            'message' => 'This is a test message from testThree',
            'identifier' => 'PersonY#23',
            'amount' => '20',
            'currency' => 'USD'
        ]);
        print_r(json_decode($r->getBody(), true));
        self::assertArrayHasKey('donation_id', json_decode($r->getBody(), true));
    }

}