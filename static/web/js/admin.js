(function(){

	var users     = {}, 
		usrSearch = $('form#user-search'), 
		usrInput  = usrSearch.find('input[type="text"]');

	usrSearch.on('submit', function(){
		$.getJSON('/admin/user/find', {username: usrInput.val(), exact: true}, function (data) {
			if(data.length >= 1){
				window.location.href = '/admin/user/'+data[0].userId + '/edit';
			}
		});
		return false;
	});
	
	usrInput.typeahead({
		items: 10,
		updater: function(username){
			if(users[username] != undefined){
				window.location.href = '/admin/user/'+users[username].userId + '/edit';
			}
			return username;
		},
		source: function (query, process) {
			return $.getJSON('/admin/user/find', {s: query}, function (data) {
				users = {}; // make sure you dont make this a local var
				var list  = [];
				console.log(users);
				for(var i=0; i<data.length; ++i){
					var match = data[i].username + ' (' + data[i].email + ')';
					users[match] = data[i];
					list.push(match);
				};
				return process(list);
			});
		}
	});

	$('#userlist').each(function(){
		
		var usrlist    = $(this),
			gamesl     = usrlist.find('select[name="game"]'),
			pagination = usrlist.find('.pagination'),
			sizesl     = usrlist.find('select[name="size"]'),
			game       = usrlist.data('game'), 
			page       = usrlist.data('page'), 
			size       = usrlist.data('size'),
			reset      = usrlist.find('#resetuserlist');
		
		var update = function(){
			window.location.href = '/admin/?game='+encodeURIComponent(game)+'&size='+encodeURIComponent(size)+'&page='+encodeURIComponent(page);
		};
		
		pagination.on('click', 'a', function(){
			page = $(this).data('page');
			update();
			return false;
		});
		
		gamesl.on('change', function(){
			page = 1;
			game = $(this).val();
			update();
			return false;
		}).val(game);
		
		sizesl.on('change', function(){
			page = 1;
			size = $(this).val();
			update();
			return false;
		}).val(size);
		
		reset.on('click', function(){
			game = '';
			size = '';
			page = 1;
			update();
		});
		
	});
	
})();