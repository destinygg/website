<?php
return array_replace_recursive ( parse_ini_file ('.version'), array (
	'env' => array(
		'date.timezone'			=> 'UTC',
		'mysql.connect_timeout' => '5',
		'max_execution_time'	=> 30,
	),
	'time' => array(
		'DSTOffset'				=> 0 // hours
	),
	'geodata'					=> __DIR__ . '/geo-ISO_3166-1-2.json',
	'regions' => array(
		'Africa'				=> DateTimeZone::AFRICA,
		'America'				=> DateTimeZone::AMERICA,
		'Antarctica'			=> DateTimeZone::ANTARCTICA,
		'Asia'					=> DateTimeZone::ASIA,
		'Atlantic'				=> DateTimeZone::ATLANTIC,
		'Australia'				=> DateTimeZone::AUSTRALIA,
		'Europe'				=> DateTimeZone::EUROPE,
		'Indian'				=> DateTimeZone::INDIAN,
		'Pacific'				=> DateTimeZone::PACIFIC
	),
	'cdn' => array(
		'domain'				=> ''
	),
	'cookie' => array(
		'domain' 				=> '',
		'life'					=> 2592000,
		'name'					=> ''
	),
	'log' => array(
		'path'					=> '',
		'level'					=> 500
	),
	'cache' => array(
		'path'					=> '',
		'prefix'				=> 'dfl_',
		'engine'				=> 'Destiny\Cache\Apc',
		'memory'				=> 'Destiny\Cache\Apc',
		'maxTTL'				=> 172800
	),
	'db' => array(
		'host'					=> '',
		'username'				=> '',
		'database' 				=> '',
		'password' 				=> '',
	),
	'meta' => array(
		'shortName'				=> 'Destiny',
		'title'					=> 'Destiny : Steven Bonnell II',
		'author'				=> 'Steven Bonnell II',
		'description'			=> 'Destiny.gg, Online streamer, primarily playing League of Legends, but I will often venture off into other topics, including but not limited to: philosophy, youtube videos, music and all sorts of wonderful pseudo-intellectualism.',
		'keywords'				=> 'Destiny.gg,Online,stream,game,pc,League of Legends',
		'video'					=> 'http://www-cdn.jtvnw.net/widgets/live_facebook_embed_player.swf?channel=destiny',
		'videoSecureUrl'		=> 'https://secure.jtvnw.net/widgets/live_facebook_embed_player.swf?channel=destiny'
	),
	'nav' => array(
		'blog'					=> '/n/',
		'twitter'				=> 'https://twitter.com/Steven_Bonnell/',
		'youtube'				=> 'http://www.youtube.com/user/remembertomorrow0',
		'reddit'				=> 'http://www.reddit.com/r/Destiny/',
		'facebook'				=> 'https://www.facebook.com/Steven.Bonnell.II',
		'schedule'				=> '/schedule'
	),
	'paypal' => array(
		'support_email'			=> 'steven.bonnell.ii@gmail.com',
		'email'					=> 'steven.bonnell.ii@gmail.com',
		'name'					=> 'Steven Bonnell II'
	),
	'lolapi' => array (
		'url' 					=> '',
		'apikey'				=> ''
	),
	// All of these should be on in the live environment
	'blocks' => array (
		'twitch'				=> true,
		'chat'					=> true,
		'stream'				=> true,
		'twittermusic' 			=> true,
		'videos'				=> true,
		'lol'					=> true,
		'pageads'				=> true,
	),
	'youtube' => array (
		'apikey'				=> '',
		'playlistId'			=> '',
		'user' 					=> ''
	),
	'googleads' => array (
		'300x250' => array (
			'google_ad_client'	=> '',
			'google_ad_slot'	=> '',
			'google_ad_width'	=> 300,
			'google_ad_height'	=> 250
		)
	),
	'google' => array (
		'calendar' => array (
			'id' => 'i54j4cu9pl4270asok3mqgdrhk@group.calendar.google.com'
		)
	),
	'lastfm' => array (
		'apikey'			=> '',
		'user'				=> 'StevenBonnellII'
	),
	'twitch' => array (
		'user'			 	=> '',
			
		'client_id'			=> '',
		'client_secret'		=> '',
			
		'redirect_uri'		=> '',
		'request_perms'		=> 'user_read',
		
		'broadcaster' => array(
			'user'			=> '',
			'request_perms' => 'channel_check_subscription+channel_subscriptions+user_read'
		)
	),
	'twitter' => array(
		'user'				=> 'Steven_Bonnell',
		'consumer_key'		=> '',
		'consumer_secret'	=> ''
	),
	'lol' => array (
		'regions' => array (
			'na' 	=> 'North America',
			'euw' 	=> 'Europe West',
			'eune' 	=> 'Europe Nordic & East',
			'br' 	=> 'Brazil',
			'kr' 	=> 'Korea'
		),
		'trackedRegions' => array(
			'na'
		),
		'summoners' => array (
			array (
				'name'			=> 'NeoDéstiny',
				'internalName'	=> 'neodéstiny',
				'id' 			=> '26077457',
				'acctId' 		=> '40774766',
				'region' 		=> 'na',
				'stats' 		=> true,
				'public'		=> true,
				'track'			=> true,
				'aggregate'		=> true
			),
			array (
				'name' 			=> 'UltimaDestiny',
				'internalName'	=> 'ultimadestiny',
				'id' 			=> '37544949',
				'acctId' 		=> '200557964',
				'region' 		=> 'na',
				'stats'			=> true,
				'public'		=> true,
				'track'			=> true,
				'aggregate'		=> true
			)
		) 
	),
	'intervals' => array(
		'Sessiongc'			=> (60*60),
		'Subscriptions'		=> (1*24*60*60)
	),
	'fantasy' => array (
		
		'season'					=> 3,
			
		// If false, all functionality is turned off
		'feature'					=> true, 
			
		// Periodically check twitch subscriptions
		'pollSubscriptionCheck'		=> false,
			
		// True: earn points for games where the champions where created before the game. 
		// False: disregard time of champion selection
		// Should always be on in live environment
		'timeAwareAggregation' 		=> false,
		
		// The intervals the cron is run at
		'intervals' => array(
			'freechamp'				=> (3*24*60*60),
			'aggregate'				=> (5*60),
			'track'					=> (7*60),
			'ingame'				=> (4*60)
		),
			
		'credit' => array (
			'scoreToCreditEarnRate' => 1.75,
		),
			
		'maxFreeChamps' 			=> 5,
			
		'team' => array (
			'maxChampions' 			=> 5,
			'minChampions' 			=> 5,
			'maxAvailableTransfers'	=> 4,
			'startCredit' 			=> 325,
			'startTransfers'		=> 3,
			'freeMultiplierPenalty'	=> 0.5,
			'maxPotentialChamps'	=> 5,
			'teammateBonusModifier'	=> 1.2
		),
		'scores' => array (
			'PARTICIPATE'			=> 1,
			'WIN' 					=> 5,
			'LOSE' 					=> 0,
		),
		'milestones' => array (
			array (
				'type'			=> 'GAMES',
				'startValue'	=> 0,
				'goalValue'		=> 3,
				'reoccuring'	=> true,
				'reward' => array (
					'type'		=> 'TRANSFER',
					'value'		=> 1
				)
			)
		)
	),
	// Length is in accordance to PHP strtotime formatting
	'commerce' => array (
		'subscriptions' => array (
			'1-MONTH-SUB' => array (
				'label' 	=> '1 Month Subscription',
				'amount' 	=> '5.00',
				'length'	=> '+1 month'	
			),
			'2-MONTH-SUB' => array (
				'label' 	=> '2 Month Subscription',
				'amount' 	=> '10.00',
				'length'	=> '+2 month'
			),
			'3-MONTH-SUB' => array (
				'label' 	=> '3 Month Subscription',
				'amount' 	=> '15.00',
				'length'	=> '+3 month'
			) 
		) 
	) 
), include 'config.php');
?>