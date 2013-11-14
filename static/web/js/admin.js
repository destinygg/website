(function(){

	var users = [], 
		usrSearch = $('form#user-search'), 
		usrInput = usrSearch.find('input[type="text"]');

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
			for(var i=0; i<users.length; ++i){
				if(users[i].username == username){
					window.location.href = '/admin/user/'+users[i].userId + '/edit';
					break;
				}
			};
			return username;
		},
		source: function (query, process) {
			return $.getJSON('/admin/user/find', {username: query}, function (data) {
				users = data;
				var list = new Array();
				for(var i=0; i<users.length; ++i){
					list.push(users[i].username);
				};
				return process(list);
			});
		}
	});

	$('#userlist').each(function(){
		
		var usrlist = $(this),
			gamesl = usrlist.find('select[name="game"]'),
			pagination = usrlist.find('.pagination'),
			sizesl = usrlist.find('select[name="size"]');
		
		var game = usrlist.data('game'), 
			page = usrlist.data('page'), 
			size = usrlist.data('size'),
			reset = usrlist.find('#resetuserlist');
		
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