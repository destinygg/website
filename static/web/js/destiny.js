destiny = {
	cdn:		'',
	token:		'',
	baseUrl: 	'/',
	timeout: 	15000,
	fn: 		{}
};
destiny.init = function(args){
	$.extend(destiny, args);
};
