<?php
return array (
	'cacheAnnotations'		=> true, // If TRUE, stores the annotation definitions in files /tmp/annotations/ (these need to be cleared if changes are made to annotations)
	'allowImpersonation'	=> false, /// MUST BE OFF ON LIVE AT ALL TIMES /impersonate?user=Cene or /impersonate?userId=12
	'useMinifiedFiles'		=> false, // Prevent using minified files
	'profile' => array(
		'nameChangeLimit' 	=> 0
	),
	'chat' => array(
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
		),
	),
	'redis' => array(
		'host' 				=> '127.0.0.1',
		'port' 				=> 6379,
		'database'			=> 0
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
		'twitter'
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
		'path' 			=> _BASEDIR . '/lib/Resources/views/'
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
	'lolapi' => array (
		'url'				=> '',
		'apikey'			=> '' 
	),
	// All of these should be on in the live environment
	'blocks' => array (
		'twitch'		=> true,
		'chat'			=> true,
		'stream'		=> true,
		'twittermusic'	=> true,
		'videos'		=> true,
		'lol'			=> true,
		'pageads'		=> true 
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
	'lol' => array (
		'regions' => array (
				'na'			=> 'North America',
				'euw'			=> 'Europe West',
				'eune'			=> 'Europe Nordic & East',
				'br'			=> 'Brazil',
				'kr'			=> 'Korea' 
		),
		'trackedRegions' => array (
				'na' 
		),
		'summoners' => array (
			array (
				'name'				=> 'NeoDéstiny',
				'internalName'		=> 'neodéstiny',
				'id'				=> '26077457',
				'acctId'			=> '40774766',
				'region'			=> 'na',
				'stats'				=> true,
				'public'			=> true,
				'track'				=> true,
				'aggregate'			=> true 
			),
			array (
				'name'				=> 'UltimaDestiny',
				'internalName'		=> 'ultimadestiny',
				'id'				=> '37544949',
				'acctId'			=> '200557964',
				'region'			=> 'na',
				'stats'				=> true,
				'public'			=> true,
				'track'				=> true,
				'aggregate'			=> true 
			) 
		) 
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
		// The KEY is the subscriptions type
		'subscriptions' => array (
			'1-MONTH-SUB' => array (
				'id'				=> '1-MONTH-SUB',
				'tier'				=> 1,
				'label'				=> 'Standard subscription',
				'agreement'			=> '$5.00 (per month) recurring subscription',
				'amount'			=> '5.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB' => array (
				'id'				=> '3-MONTH-SUB',
				'tier'				=> 1,
				'label'				=> 'Value subscription',
				'agreement'			=> '$12.00 (per 3 months) recurring subscription',
				'amount'			=> '12.00',
				'billingFrequency'	=> 3,
				'billingPeriod'		=> 'Month' 
			),
			'1-MONTH-SUB2' => array (
				'id'				=> '1-MONTH-SUB2',
				'tier'				=> 2,
				'label'				=> 'Premium subscription',
				'agreement'			=> '$10.00 (per month) recurring subscription',
				'amount'			=> '10.00',
				'billingFrequency'	=> 1,
				'billingPeriod'		=> 'Month' 
			),
			'3-MONTH-SUB2' => array (
				'id'				=> '3-MONTH-SUB2',
				'tier'				=> 2,
				'label'				=> 'Premium value subscription',
				'agreement'			=> '$24.00 (per 3 months) recurring subscription',
				'amount'			=> '24.00',
				'billingFrequency'	=> 3,
				'billingPeriod'		=> 'Month' 
			) 
		)
	),
	'scheduler'=>array(
		'frequency' => 1,
		'period' => 'minute',
		'schedule' => array (
			array (
				'action' => 'SubscriptionExpire',
				'lastExecuted' => null,
				'frequency' => 30,
				'period' => 'minute',
				'executeOnNextRun' => false
			),
			array (
				'action' => 'LastFmFeed',
				'lastExecuted' => null,
				'frequency' => 1,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			array (
				'action' => 'YoutubeFeed',
				'lastExecuted' => null,
				'frequency' => 30,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			array (
				'action' => 'BroadcastsFeed',
				'lastExecuted' => null,
				'frequency' => 30,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			array (
				'action' => 'TwitterFeed',
				'lastExecuted' => null,
				'frequency' => 30,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			array (
				'action' => 'SummonersFeed',
				'lastExecuted' => null,
				'frequency' => 10,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			array (
				'action' => 'CalendarEvents',
				'lastExecuted' => null,
				'frequency' => 60,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			array (
				'action' => 'BlogFeed',
				'lastExecuted' => null,
				'frequency' => 60,
				'period' => 'minute',
				'executeOnNextRun' => true
			),
			array (
				'action' => 'StreamInfo',
				'lastExecuted' => null,
				'frequency' => 1,
				'period' => 'minute',
				'executeOnNextRun' => true
			)
		)
	)
);
?>