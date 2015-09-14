<?php
return array (
	// the key is the LOWERCASE domain to blacklist, the value is something non-null
	'blacklistedDomains' => array(
	),
	'cacheAnnotations'		=> true, // If TRUE, stores the annotation definitions in files /tmp/annotations/ (these need to be cleared if changes are made to annotations)
	'allowImpersonation'	=> false, // MUST BE OFF ON LIVE AT ALL TIMES. usage: /impersonate?user=Cene or /impersonate?userId=12
	'profile' => array(
		'nameChangeLimit' 	=> 0
	),
	'privateKeys' => array(
		'chat' => '',
		'minecraft' => '',
	),
	'chat' => array(
		'host'				=> @$_SERVER['SERVER_NAME'],
		'port'				=> 9998,
		'backlog' 			=> 150,
		'maxlines' 			=> 150,
		'privatekey'		=> null,
		'customemotes' => array(
			'Dravewin',
			'INFESTINY',
			'FIDGETLOL',
			'Hhhehhehe',
			'GameOfThrows',
			'WORTH',
			'FeedNathan',
			'Abathur',
			'LUL',
			'Heimerdonger',
			'SoSad',
			'DURRSTINY',
			'SURPRISE',
			'NoTears',
			'OverRustle',
			'DuckerZ',
			'Kappa',
			'Klappa',
			'DappaKappa',
			'BibleThump',
			'AngelThump',
			'FrankerZ',
			'BasedGod',
			'TooSpicy',
			'OhKrappa',
			'SoDoge',
			'WhoahDude',
			'MotherFuckinGame',
			'DaFeels',
			'UWOTM8',
			'CallCatz',
			'CallChad',
			'DatGeoff',
			'Disgustiny',
			'FerretLOL',
			'Sippy',
			'DestiSenpaii',
			'Nappa',
			'DAFUK',
			'AYYYLMAO',
			'DANKMEMES',
			'MLADY',
			'SOTRIGGERED',
			'MASTERB8',
			'NOTMYTEMPO',
			'LIES',
			'LeRuse',
			'YEE',
			'SWEATSTINY',
			'PEPE',
			'CheekerZ',
			'SpookerZ',
			'SLEEPSTINY',
		),
		'twitchemotes' => array(
			'nathanDad',
			'nathanFather',
			'nathanDubs1',
			'nathanDubs2',
			'nathanDubs3',
			'nathanParty',
			'nathanDank',
			'nathanFeels',
		),
	),
	'redis' => array(
		'host' 				=> 'localhost',
		'port' 				=> 6379,
		'database'			=> 0,
		'scriptdir'			=> _BASEDIR . '/scripts/redis/',
	),
	'curl' => array(
		'verifypeer' 		=> false,
		'timeout'			=> 30,
		'connecttimeout'	=> 5
	),
	'authProfiles' => array (
		'twitch',
		'google',
		'twitter',
		'reddit'
	),
	'oauth' => array(
		'callback' 						=> '/%s',
		'providers' => array(
			'google' => array (
				'clientId'				=> '',
				'clientSecret'			=> ''
			),
			'twitch' => array (
				'clientId'				=> '',
				'clientSecret'			=> ''
			),
			'twitter' => array (
				'clientId'				=> '',
				'clientSecret'			=> '',
				'token' 				=> '',
				'secret'				=> ''
			),
			'reddit' => array (
				'clientId'				=> '',
				'clientSecret'			=> '',
				'token' 				=> '',
				'secret'				=> ''
			)
		)
	),
	'regions' => array (
		'Africa'		=> DateTimeZone::AFRICA,
		'America'		=> DateTimeZone::AMERICA,
		'Antarctica'	=> DateTimeZone::ANTARCTICA,
		'Asia'			=> DateTimeZone::ASIA,
		'Atlantic'		=> DateTimeZone::ATLANTIC,
		'Australia'		=> DateTimeZone::AUSTRALIA,
		'Europe'		=> DateTimeZone::EUROPE,
		'Indian'		=> DateTimeZone::INDIAN,
		'Pacific'		=> DateTimeZone::PACIFIC 
	),
	'cdn' => array (
		'domain' 		=> '' 
	),
	'cookie' => array (
		'domain' 		=> '',
		'path' 			=> '/',
		'secure'        => false,
		'httponly'		=> true,
	),
	'tpl' => array (
		'path' 			=> _BASEDIR . '/lib/Resources/views/',
		'error.path' 	=> _BASEDIR . '/',
	),
	'geodata' => array (
		'json'			=> _BASEDIR . '/lib/Resources/geo-ISO_3166-1-2.json'
	),
	'log' => array (
		'path' 			=> _BASEDIR . '/log/'
	),
	'cache' => array (
		'path'			=> _BASEDIR . '/tmp/',
		'namespace'     => '_destinygg_web'
	),
	'db' => array (
		'driver'		=> 'pdo_mysql',
		'host'			=> '',
		'user'			=> '',
		'dbname'		=> '',
		'password'		=> '',
		'charset'		=> 'UTF8',
		'driverOptions'	=> array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8,time_zone = \'+0:00\'')
	),
	'meta' => array (
		'shortName'			=> 'Destiny',
		'title'				=> 'Destiny - Steven Bonnell II',
		'author'			=> 'Steven Bonnell II',
		'description'		=> 'Steven (Destiny) Bonnell II is a professional streamer, primarily playing StarCraft II, but will often venture off into other topics, including but not limited to: philosophy, youtube videos, music and all sorts of wonderful pseudo-intellectualism.',
		'shortdescription'  => 'Destiny is a professional streamer, primarily playing StarCraft II.',
		'keywords'			=> 'Steven Bonnell,Destiny,Destiny.gg,StarCraft,StarCraft2,Counter Strike,CS:GO,League of Legends,streamer,stream,game,pc,build a box',
		'video'				=> 'http://www-cdn.jtvnw.net/widgets/live_facebook_embed_player.swf?channel=destiny',
		'videoSecureUrl'	=> 'https://secure.jtvnw.net/widgets/live_facebook_embed_player.swf?channel=destiny' 
	),
	'paypal' => array (
		'support_email'		=> 'support@destiny.gg',
		'email'				=> 'support@destiny.gg',
		'name'				=> 'Destiny.gg',
		'api' => array (
			'endpoint'		=> '',
			'ipn'			=> ''
		)
	),
	'youtube' => array (
		'apikey'		=> '',
		'playlistId'	=> '',
		'user'			=> '' 
	),
	'analytics' => array (
		'account' 				=> '',
		'domainName' 			=> ''
	),
	'googleads' => array (
		'300x250' => array (
			'google_ad_client'	=> '',
			'google_ad_slot'	=> '',
			'google_ad_width'	=> 300,
			'google_ad_height'	=> 250 
		) 
	),
	'g-recaptcha' => array (
		'key'                   => '',
		'secret'                => '',
	),
	'calendar' 					=>  '', 
	'lastfm' => array (
		'apikey'				=> '',
		'user'					=> '' 
	),
	'twitch' => array (
		'user'					=> '',
		'client_id'				=> '',
		'client_secret'			=> '',
		'broadcasterAuth'		=> false,
		'broadcaster' => array (
			'user'				=> ''
		) 
	),
	'twitter' => array (
		'user'					=> '',
		'consumer_key'			=> '',
		'consumer_secret'		=> '' 
	),
	'subscriptionType'				=> 'destiny.gg',
	'commerce' => array (
		'currencies' => array (
			'USD' => array (
				'code'				=> 'USD',
				'symbol'			=> '$' 
			) 
		),
		'reciever' => array (
				'brandName'			=> 'Destiny.gg - Subscriptions' 
		),
		'receiver_email'			=> '',
		'currency'					=> 'USD',
		'subscriptions' => array (
			'1-MONTH-SUB' => array (
				'id'				=> '1-MONTH-SUB',
				'tier'				=> 1,
				'tierLabel'			=> 'Tier I',
				'itemLabel'			=> 'Tier 1 (1 month)',
				'agreement'			=> '$5.00 (per month) recurring subscription',
				'amount'			=> '5.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB' => array (
				'id'				=> '3-MONTH-SUB',
				'tier'				=> 1,
				'tierLabel'			=> 'Tier I',
				'itemLabel'			=> 'Tier 1 (3 month)',
				'agreement'			=> '$12.00 (per 3 months) recurring subscription',
				'amount'			=> '12.00',
				'billingFrequency'	=> 3,
				'billingPeriod'		=> 'Month' 
			),
			'1-MONTH-SUB2' => array (
				'id'				=> '1-MONTH-SUB2',
				'tier'				=> 2,
				'tierLabel'			=> 'Tier II',
				'itemLabel'			=> 'Tier 2 (1 month)',
				'agreement'			=> '$10.00 (per month) recurring subscription',
				'amount'			=> '10.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB2' => array (
				'id'				=> '3-MONTH-SUB2',
				'tier'				=> 2,
				'tierLabel'			=> 'Tier II',
				'itemLabel'			=> 'Tier 2 (3 month)',
				'agreement'			=> '$24.00 (per 3 months) recurring subscription',
				'amount'			=> '24.00',
				'billingFrequency'	=> 3,
				'billingPeriod'		=> 'Month' 
			) ,
			'1-MONTH-SUB3' => array (
				'id'				=> '1-MONTH-SUB3',
				'tier'				=> 3,
				'tierLabel'			=> 'Tier III',
				'itemLabel'			=> 'Tier 3 (1 month)',
				'agreement'			=> '$20.00 (per month) recurring subscription',
				'amount'			=> '20.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB3' => array (
				'id'				=> '3-MONTH-SUB3',
				'tier'				=> 3,
				'tierLabel'			=> 'Tier III',
				'itemLabel'			=> 'Tier 3 (3 month)',
				'agreement'			=> '$48.00 (per 3 months) recurring subscription',
				'amount'			=> '48.00',
				'billingFrequency'	=> 3,
				'billingPeriod'		=> 'Month' 
			) ,
			'1-MONTH-SUB4' => array (
				'id'				=> '1-MONTH-SUB4',
				'tier'				=> 4,
				'tierLabel'			=> 'Tier IV',
				'itemLabel'			=> 'Tier 4 (1 month)',
				'agreement'			=> '$40.00 (per month) recurring subscription',
				'amount'			=> '40.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB4' => array (
				'id'				=> '3-MONTH-SUB4',
				'tier'				=> 4,
				'tierLabel'			=> 'Tier IV',
				'itemLabel'			=> 'Tier 4 (3 month)',
				'agreement'			=> '$96.00 (per 3 months) recurring subscription',
				'amount'			=> '96.00',
				'billingFrequency'	=> 3,
				'billingPeriod'		=> 'Month' 
			) 
		)
	),
	'scheduler' => array(
		'frequency' => 1,
		'period' => 'minute',
		'schedule' => array (
			'SubscriptionExpire' => array (
				'action' => 'SubscriptionExpire',
				'lastExecuted' => null,
				'frequency' => 30,
				'period' => 'minute',
				'executeOnNextRun' => false
			),
			'LastFmFeed' => array (
				'action' => 'LastFmFeed',
				'lastExecuted' => null,
				'frequency' => 1,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			'YoutubeFeed' => array (
				'action' => 'YoutubeFeed',
				'lastExecuted' => null,
				'frequency' => 30,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			'BroadcastsFeed' => array (
				'action' => 'BroadcastsFeed',
				'lastExecuted' => null,
				'frequency' => 30,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			'TwitterFeed' => array (
				'action' => 'TwitterFeed',
				'lastExecuted' => null,
				'frequency' => 30,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			'BlogFeed' => array (
				'action' => 'BlogFeed',
				'lastExecuted' => null,
				'frequency' => 60,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			'StreamInfo' => array (
				'action' => 'StreamInfo',
				'lastExecuted' => null,
				'frequency' => 1,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			'RedditSubscribers' => array (
				'action' => 'RedditSubscribers',
				'lastExecuted' => null,
				'frequency' => 1,
				'period' => 'hour',
				'executeOnNextRun' => true,
				'output' => ''
			)
		)
	)
);
?>