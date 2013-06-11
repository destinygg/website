$(function(){

	var adminTabs = $('#admintabs');
	
	var ajaxSettings = {
		type: 'POST',
		success: function(response){
			window.location.reload();
		}
	};
	
	adminTabs.find('.btn-cron-action').on('click', function(){
		var btn = $(this), action = btn.attr('rel');
		btn.attr('disabled','disabled');
		$.ajax($.extend({}, ajaxSettings, {
			type: 'get',
			data: {id:action},
			url: destiny.baseUrl + 'Admin/Cron',
			success: function(data){
				btn.removeAttr('disabled');
				if(confirm('Response: '+ data.message + "\r\n" + "Refresh?")){
					window.location.reload();
				}
			}
		}));
	});
	
	adminTabs.find('#Games .btn-reset').on('click', function(){
		if(!confirm('Are you sure?')){
			return false;
		}
		var btn = $(this), gameId = btn.attr('rel');
		btn.attr('disabled','disabled');
		$.ajax($.extend({}, ajaxSettings, {
			type: 'get',
			data: {'gameId':gameId},
			url: destiny.baseUrl + 'Admin/ResetGame',
			success: function(data){
				window.location.reload();
			}
		}));
		return false;
	});
	
});