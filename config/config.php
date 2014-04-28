<?php
return array (
	'cacheAnnotations'		=> true, // If TRUE, stores the annotation definitions in files /tmp/annotations/ (these need to be cleared if changes are made to annotations)
	'allowImpersonation'	=> false, // MUST BE OFF ON LIVE AT ALL TIMES. usage: /impersonate?user=Cene or /impersonate?userId=12
	'showExceptions'		=> false, // Shows the actual exception message, instead of a generic one in the error screen
	'maintenance'			=> false,
	'profile' => array(
		'nameChangeLimit' 	=> 0
	),
	'chat' => array(
		'host'				=> @$_SERVER['SERVER_NAME'],
		'port'				=> 9998,
		'backlog' 			=> 150,
		'maxlines' 			=> 150,
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
			'ASLAN',
			'DJAslan',
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
			'DESBRO',
			'MotherFuckinGame',
			'DaFeels',
			'UWOTM8',
			'CallCatz',
			'CallChad',
			'DatGeoff',
			'Disgustiny',
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
	'rememberme' => array(
		'cookieName'		=> 'rememberme'
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
		'name' 			=> 'sid',
		'path' 			=> '/' 
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
		'path'			=> _BASEDIR . '/tmp/'
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
		'title'				=> 'Destiny : Steven Bonnell II',
		'author'			=> 'Steven Bonnell II',
		'description'		=> 'Destiny.gg, Online streamer, primarily playing League of Legends, but I will often venture off into other topics, including but not limited to: philosophy, youtube videos, music and all sorts of wonderful pseudo-intellectualism.',
		'keywords'			=> 'Destiny.gg,Online,stream,game,pc,League of Legends',
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
				'itemLabel'			=> 'Standard subscription',
				'agreement'			=> '$5.00 (per month) recurring subscription',
				'amount'			=> '5.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB' => array (
				'id'				=> '3-MONTH-SUB',
				'tier'				=> 1,
				'tierLabel'			=> 'Tier I',
				'itemLabel'			=> 'Value subscription',
				'agreement'			=> '$12.00 (per 3 months) recurring subscription',
				'amount'			=> '12.00',
				'billingFrequency'	=> 3,
				'billingPeriod'		=> 'Month' 
			),
			'1-MONTH-SUB2' => array (
				'id'				=> '1-MONTH-SUB2',
				'tier'				=> 2,
				'tierLabel'			=> 'Tier II',
				'itemLabel'			=> 'Standard subscription',
				'agreement'			=> '$10.00 (per month) recurring subscription',
				'amount'			=> '10.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB2' => array (
				'id'				=> '3-MONTH-SUB2',
				'tier'				=> 2,
				'tierLabel'			=> 'Tier II',
				'itemLabel'			=> 'Value subscription',
				'agreement'			=> '$24.00 (per 3 months) recurring subscription',
				'amount'			=> '24.00',
				'billingFrequency'	=> 3,
				'billingPeriod'		=> 'Month' 
			) ,
			'1-MONTH-SUB3' => array (
				'id'				=> '1-MONTH-SUB3',
				'tier'				=> 3,
				'tierLabel'			=> 'Tier III',
				'itemLabel'			=> 'Standard subscription',
				'agreement'			=> '$20.00 (per month) recurring subscription',
				'amount'			=> '20.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB3' => array (
				'id'				=> '3-MONTH-SUB3',
				'tier'				=> 3,
				'tierLabel'			=> 'Tier III',
				'itemLabel'			=> 'Value subscription',
				'agreement'			=> '$48.00 (per 3 months) recurring subscription',
				'amount'			=> '48.00',
				'billingFrequency'	=> 3,
				'billingPeriod'		=> 'Month' 
			) ,
			'1-MONTH-SUB4' => array (
				'id'				=> '1-MONTH-SUB4',
				'tier'				=> 4,
				'tierLabel'			=> 'Tier IIII',
				'itemLabel'			=> 'Standard subscription',
				'agreement'			=> '$40.00 (per month) recurring subscription',
				'amount'			=> '40.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB4' => array (
				'id'				=> '3-MONTH-SUB4',
				'tier'				=> 4,
				'tierLabel'			=> 'Tier IIII',
				'itemLabel'			=> 'Value subscription',
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