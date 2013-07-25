(function(){

	var users = [], f = $('#user-search'), inpt = f.find('input[type="text"]');

	f.on('submit', function(){
		$.getJSON('/admin/user/find', {username: inpt.val(), exact: true}, function (data) {
			if(data.length >= 1){
				window.location.href = '/admin/user/'+data[0].userId + '/edit';
			}
		});
		return false;
	});
	inpt.typeahead({
		items: 20,
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

})();