<?php

//ini_set('log_errors', 1);
//ini_set('error_log', _BASEDIR . '/log/php.log');
//ini_set('error_reporting', E_ALL ^ (E_NOTICE | E_WARNING | E_STRICT | E_DEPRECATED));
//ini_set('date.timezone', 'UTC');

return [
    'cacheNamespace' => '_web11',
    'crypto' => [                   // the key and seed should be a minimum of 32 random generated bytes
        'key' => '***REPLACE***',   // used as the key for encryption
        'seed' => '***REPLACE***',  // used as the seed for hashing like hmac
    ],
    'allowImpersonation' => false,  // MUST BE OFF ON LIVE AT ALL TIMES. usage: /impersonate?user=Cene or /impersonate?userId=12

    'embed' => [
        'stream' => '',
        'chat' => '/embed/chat'
    ],

    'overrustle' => [
        'stalk' => '',
        'mentions' => ''
    ],

    'cdn' => ['domain' => '','protocol' => 'https://'],
    'blog' => ['feed' => ''],
    'reddit' => ['threads' => ''],
    'android' => ['app' => ''],
    'meta' => [],
    'links' => [],
    'support_email' => '',
    'google-verification' => '',
    'calendar' => '',

    'privateKeys' => [
        'chat' => '',
        'api' => '',
    ],

    'images' => [
        'path' => _BASEDIR . '/static/cache/',
        'uri' => '/cache',
    ],

    'redis' => [
        'host' => 'localhost',
        'port' => 6379,
        'database' => 0,
        'scriptdir' => _BASEDIR . '/scripts/redis/',
    ],

    'authProfiles' => [
        'twitch',
        'google',
        'twitter',
        'reddit',
        'discord'
    ],

    'oauth_providers' => [
        'google' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => ''
        ],
        'twitch' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => ''
        ],
        'twitter' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => ''
        ],
        'reddit' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => ''
        ],
        'discord' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => ''
        ],
        'streamlabs' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => ''
        ],
        'twitchbroadcaster' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => '',
            'user_id' => -1
        ]
    ],

    'cookie' => [
        'domain' => '',
        'path' => '/',
        'secure' => false,
        'httponly' => true,
    ],

    'db' => [
        'driver'        => 'pdo_mysql',
        'host'          => 'localhost',
        'user'          => '',
        'dbname'        => '',
        'password'      => '',
        'charset'       => 'UTF8',
        'driverOptions' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8,time_zone = \'+0:00\'']
    ],

    'paypal' => [
        'endpoint_checkout' => 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=',
        'endpoint_ipn' => 'http://localhost/ipn/',
        'sdk' => [
            'mode'                      => 'sandbox',
            'acct1.ClientId'            => '',
            'acct1.ClientSecret'        => '',
            'acct1.UserName'            => '',
            'acct1.Password'            => '',
            'acct1.Signature'           => '',
            'acct1.CertPath'            => '',
            'service.Endpoint.IPN'      => 'http://localhost/ipn/',
            'log.LogEnabled'            => true,
            'log.FileName'              => _BASEDIR . 'log/paypal.log',
            'log.LogLevel'              => 'ERROR',
            'http.ConnectionTimeOut'    => 10,
            'http.Retry'                => 1,
        ]
    ],

    'youtube' => [
        'apikey' => '',
        'playlistId' => '',
        'user' => ''
    ],

    'analytics' => [
        'account' => '',
        'domainName' => ''
    ],

    'googleads' => [
        '300x250' => [
            'google_ad_client' => '',
            'google_ad_slot' => ''
        ]
    ],

    'g-recaptcha' => [
        'key' => '',
        'secret' => '',
    ],

    'lastfm' => [
        'apikey' => '',
        'user' => ''
    ],

    'twitch' => [
        'id' => -1,
        'user' => ''
    ],

    'twitter' => [
        'user' => '',
    ],

    'streamlabs' => [
        'default_user' => -1,
        'alert_donations' => true,
        'alert_subscriptions' => true,
        'send_donations' => true
    ],

    'subscriptionType' => 'destiny.gg',

    'commerce' => [
        'receiver_email' => '',
        'currency' => 'USD',
        'minimum_donation' => 5,
        'subscriptions' => [
            '1-MONTH-SUB' => [
                'id'                => '1-MONTH-SUB',
                'tier'              => 1,
                'tierLabel'         => 'Tier I',
                'itemLabel'         => 'Tier 1 (1 month)',
                'agreement'         => '$5.00 (per month) recurring subscription',
                'amount'            => '5.00',
                'billingFrequency'  => 1,
                'billingPeriod'     => 'Month'
            ],
            '3-MONTH-SUB' => [
                'id'                => '3-MONTH-SUB',
                'tier'              => 1,
                'tierLabel'         => 'Tier I',
                'itemLabel'         => 'Tier 1 (3 month)',
                'agreement'         => '$12.00 (per 3 months) recurring subscription',
                'amount'            => '12.00',
                'billingFrequency'  => 3,
                'billingPeriod'     => 'Month'
            ],
            '1-MONTH-SUB2' => [
                'id'                => '1-MONTH-SUB2',
                'tier'              => 2,
                'tierLabel'         => 'Tier II',
                'itemLabel'         => 'Tier 2 (1 month)',
                'agreement'         => '$10.00 (per month) recurring subscription',
                'amount'            => '10.00',
                'billingFrequency'  => 1,
                'billingPeriod'     => 'Month'
            ],
            '3-MONTH-SUB2' => [
                'id'                => '3-MONTH-SUB2',
                'tier'              => 2,
                'tierLabel'         => 'Tier II',
                'itemLabel'         => 'Tier 2 (3 month)',
                'agreement'         => '$24.00 (per 3 months) recurring subscription',
                'amount'            => '24.00',
                'billingFrequency'  => 3,
                'billingPeriod'     => 'Month'
            ],
            '1-MONTH-SUB3' => [
                'id'                => '1-MONTH-SUB3',
                'tier'              => 3,
                'tierLabel'         => 'Tier III',
                'itemLabel'         => 'Tier 3 (1 month)',
                'agreement'         => '$20.00 (per month) recurring subscription',
                'amount'            => '20.00',
                'billingFrequency'  => 1,
                'billingPeriod'     => 'Month'
            ],
            '3-MONTH-SUB3' => [
                'id'                => '3-MONTH-SUB3',
                'tier'              => 3,
                'tierLabel'         => 'Tier III',
                'itemLabel'         => 'Tier 3 (3 month)',
                'agreement'         => '$48.00 (per 3 months) recurring subscription',
                'amount'            => '48.00',
                'billingFrequency'  => 3,
                'billingPeriod'     => 'Month'
            ],
            '1-MONTH-SUB4' => [
                'id'                => '1-MONTH-SUB4',
                'tier'              => 4,
                'tierLabel'         => 'Tier IV',
                'itemLabel'         => 'Tier 4 (1 month)',
                'agreement'         => '$40.00 (per month) recurring subscription',
                'amount'            => '40.00',
                'billingFrequency'  => 1,
                'billingPeriod'     => 'Month'
            ],
            '3-MONTH-SUB4' => [
                'id'                => '3-MONTH-SUB4',
                'tier'              => 4,
                'tierLabel'         => 'Tier IV',
                'itemLabel'         => 'Tier 4 (3 month)',
                'agreement'         => '$96.00 (per 3 months) recurring subscription',
                'amount'            => '96.00',
                'billingFrequency'  => 3,
                'billingPeriod'     => 'Month'
            ]
        ]
    ]
];
