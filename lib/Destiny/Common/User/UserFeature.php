<?php
namespace Destiny\Common\User;

abstract class UserFeature {
    
    const PROTECT = 'protected';
    const SUBSCRIBER = 'subscriber';
    const SUBSCRIBERT0 = 'flair9'; // twitch subscriber
    const SUBSCRIBERT1 = 'flair13';
    const SUBSCRIBERT2 = 'flair1';
    const SUBSCRIBERT3 = 'flair3';
    const SUBSCRIBERT4 = 'flair8';
    const VIP = 'vip';
    const MODERATOR = 'moderator';
    const ADMIN = 'admin';
    const BROADCASTER = 'flair12';
    const BOT = 'bot';
    const BOT2 = 'flair11';
    const NOTABLE = 'flair2';
    const TRUSTED = 'flair4';
    const CONTRIBUTOR = 'flair5';
    const COMPCHALLENGE = 'flair6';
    const EVE = 'flair7';
    const SC2 = 'flair10';
    const MINECRAFTVIP = 'flair14';

    public static $FEATURE_MAP = [
        'PROTECTED'     => self::PROTECT,
        'SUBSCRIBER'    => self::SUBSCRIBER,
        'SUBSCRIBERT0'  => self::SUBSCRIBERT0,
        'SUBSCRIBERT1'  => self::SUBSCRIBERT1,
        'SUBSCRIBERT2'  => self::SUBSCRIBERT2,
        'SUBSCRIBERT3'  => self::SUBSCRIBERT3,
        'SUBSCRIBERT4'  => self::SUBSCRIBERT4,
        'VIP'           => self::VIP,
        'MODERATOR'     => self::MODERATOR,
        'ADMIN'         => self::ADMIN,
        'BROADCASTER'   => self::BROADCASTER,
        'BOT'           => self::BOT,
        'BOT2'          => self::BOT2,
        'NOTABLE'       => self::NOTABLE,
        'TRUSTED'       => self::TRUSTED,
        'CONTRIBUTOR'   => self::CONTRIBUTOR,
        'COMPCHALLENGE' => self::COMPCHALLENGE,
        'EVE'           => self::EVE,
        'SC2'           => self::SC2
    ];

    public static $FEATURES = [
        self::PROTECT,
        self::SUBSCRIBER,
        self::SUBSCRIBERT0,
        self::SUBSCRIBERT1,
        self::SUBSCRIBERT2,
        self::SUBSCRIBERT3,
        self::SUBSCRIBERT4,
        self::VIP,
        self::MODERATOR,
        self::ADMIN,
        self::BROADCASTER,
        self::BOT,
        self::BOT2,
        self::NOTABLE,
        self::TRUSTED,
        self::CONTRIBUTOR,
        self::COMPCHALLENGE,
        self::EVE,
        self::SC2,
        self::MINECRAFTVIP
    ];

    public static $PSEUDO_FEATURES = [
        self::SUBSCRIBER,
        self::SUBSCRIBERT0,
        self::SUBSCRIBERT1,
        self::SUBSCRIBERT2,
        self::SUBSCRIBERT3,
        self::SUBSCRIBERT4
    ];

    public static $NON_PSEUDO_FEATURES = [
        self::PROTECT,
        self::VIP,
        self::MODERATOR,
        self::ADMIN,
        self::BROADCASTER,
        self::BOT,
        self::BOT2,
        self::NOTABLE,
        self::TRUSTED,
        self::CONTRIBUTOR,
        self::COMPCHALLENGE,
        self::EVE,
        self::SC2,
        self::MINECRAFTVIP
    ];

}