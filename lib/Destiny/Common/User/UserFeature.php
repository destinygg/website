<?php
namespace Destiny\Common\User;

abstract class UserFeature {
    
    const PROTECT = 'protected';
    const SUBSCRIBER = 'subscriber';
    const SUBSCRIBERT0 = 'flair9'; // twitch subscriber
    const SUBSCRIBERT2 = 'flair1';
    const SUBSCRIBERT3 = 'flair3';
    const SUBSCRIBERT4 = 'flair8';
    const VIP = 'vip';
    const MODERATOR = 'moderator';
    const ADMIN = 'admin';
    const BOT = 'bot';
    const NOTABLE = 'flair2';
    const TRUSTED = 'flair4';
    const CONTRIBUTOR = 'flair5';
    const COMPCHALLENGE = 'flair6';
    const EVENOTABLE = 'flair7';

    public static $FEATURES = [
        self::PROTECT,
        self::SUBSCRIBER,
        self::SUBSCRIBERT0,
        self::SUBSCRIBERT2,
        self::SUBSCRIBERT3,
        self::SUBSCRIBERT4,
        self::VIP,
        self::MODERATOR,
        self::ADMIN,
        self::BOT,
        self::NOTABLE,
        self::TRUSTED,
        self::CONTRIBUTOR,
        self::COMPCHALLENGE,
        self::EVENOTABLE
    ];

    public static $PSEUDO_FEATURES = [
        self::SUBSCRIBER,
        self::SUBSCRIBERT2,
        self::SUBSCRIBERT2,
        self::SUBSCRIBERT3,
        self::SUBSCRIBERT4
    ];

    public static $NON_PSEUDO_FEATURES = [
        self::PROTECT,
        self::VIP,
        self::MODERATOR,
        self::ADMIN,
        self::BOT,
        self::NOTABLE,
        self::TRUSTED,
        self::CONTRIBUTOR,
        self::COMPCHALLENGE,
        self::EVENOTABLE
    ];

}