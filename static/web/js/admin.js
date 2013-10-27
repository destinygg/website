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

	
})();

(function(){

	var broadcastUi = $('#broadcastui');
		broadcastForm = $('form#broadcast'), 
		broadcastInput = broadcastForm.find('input[type="text"]'),
		msgUi = null;

	broadcastForm.on('submit', function(e){
		$.ajax({
			url: '/admin/broadcast',
			type: 'post',
			data: {message: broadcastInput.val()},
			success: function(data){
				if(msgUi)
					msgUi.remove();
				msgUi = $(
					'<div id="broadcast-msg" class="alert alert-info" style="margin-top: 1em;">'+
						'<button type="button" class="close persist" data-dismiss="alert">&times;</button>'+
						'<h4>Broadcast sent</h4>'+
						'<p style="margin-bottom: 0;">Message: <span id="msgtext"></span></p>'+
					'</div>'
				);
				msgUi.find('#msgtext').text('"' + data.message + '"');
				broadcastUi.append(msgUi);
				broadcastInput.val('');
				broadcastInput.focus();
			}
		});
		return false;
	});

	
})();