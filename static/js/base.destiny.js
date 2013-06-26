destiny = {
	cdn:		'',
	token:		'',
	baseUrl: 	'/',
	timeout: 	15000	
};
destiny.urls = {
	lastfm: 		destiny.baseUrl + 'Lastfm.json',
	twitter: 		destiny.baseUrl + 'Twitter.json',
	youtube: 		destiny.baseUrl + 'Youtube.json',
	summonerstats:	destiny.baseUrl + 'Summoners.json',
	broadcasts: 	destiny.baseUrl + 'Broadcasts.json',
	stream: 		destiny.baseUrl + 'Stream.json',
	fantasyteam:	destiny.baseUrl + 'Fantasy/Team.json',
	ingame:			destiny.baseUrl + 'Fantasy/Ingame.json',
};
destiny.polling = {
	lastfm: 		40,
	twitter: 		600,
	stream: 		30,
	adsRotate: 		6,
	summonerstats: 	70,
	fantasyteam: 	60,
	broadcasts: 	900,
	ingame: 		60
};
destiny.init = function(args){
	$.extend(destiny, args);
};