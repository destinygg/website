<?php
namespace Destiny\Common\User;

abstract class UserFeature {
    
    const SUBSCRIBER = 'subscriber';
    const SUBSCRIBER_TWITCH = 'flair9';
    const SUBSCRIBERT1 = 'flair13';
    const SUBSCRIBERT2 = 'flair1';
    const SUBSCRIBERT3 = 'flair3';
    const SUBSCRIBERT4 = 'flair8';
    const DGGBDAY = 'flair15';
    const MINECRAFTVIP = 'flair14';

    public static $UNASSIGNABLE = [
        self::SUBSCRIBER,
        self::SUBSCRIBER_TWITCH,
        self::SUBSCRIBERT1,
        self::SUBSCRIBERT2,
        self::SUBSCRIBERT3,
        self::SUBSCRIBERT4,
        self::DGGBDAY,
    ];

}