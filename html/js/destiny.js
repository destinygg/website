destiny = {
	cdn:		'',
	token:		'',
	baseUrl: 	'/',
	timout: 	15000	
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
	lastfm: 		15,
	twitter: 		600,
	stream: 		15,
	adsRotate: 		6,
	summonerstats: 	45,
	fantasyteam: 	30,
	broadcasts: 	900,
	polls: 			30,
	ingame: 		30
};
destiny.init = function(args){
	$.extend(destiny, args);
};