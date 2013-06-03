$(function(){
	
	$('form.challengeForm').on('submit', function(){
		var form = $(this),
			btn = form.find('button:first'), 
			name = form.find('input[name="name"]');
		if(name.val().replace(/^\s\s*/, '').replace(/\s\s*$/, '') == ''){
			return false;
		}
		name.attr('disabled','disabled');
		btn.removeClass('btn-inverse').addClass('btn-primary');
		btn.text('Sending...');
		btn.attr('disabled','disabled');
		name.attr('disabled','disabled');
		$.ajax({
			type: 'get',
			data: {'name':name.val()},
			url: destiny.baseUrl + 'Fantasy/Challenge',
			success: function(data){
				name.removeAttr('disabled');
				btn.removeAttr('disabled');
				btn.removeClass('btn-primary');
				btn.text(data.message);
				if(data.success){
					btn.addClass('btn-success');
				}else{
					btn.addClass('btn-danger');
				}
				window.setTimeout(function(){
					btn.text('Challenge!');
					btn.removeClass('btn-primary btn-danger').addClass('btn-inverse');
					name.val('');
				},2000);
			},
			error: function(){
				btn.addClass('btn-danger');
				window.setTimeout(function(){
					btn.text('Challenge!');
					btn.removeClass('btn-primary btn-danger').addClass('btn-inverse');
					name.val('');
				},2000);
			}
		});
		return false;
	});
	
	$('#challengeInvites').each(function(){
		var ui = $(this);
		ui.find('.invite-accept').on('click', function(){
			var btn = $(this);
			if(confirm('Are you sure you want to accept this invite?')){
				btn.attr('disabled','disabled');
				$.ajax({
					type: 'get',
					data: {'teamId':btn.data('teamid')},
					url: destiny.baseUrl + 'Fantasy/Challenge/Accept',
					success: function(data){
						btn.closest('tr').replaceWith('<tr><td>-</td></tr>');
					},
					error: function(){
						alert('An error occured.');
						btn.removeAttr('disabled');
					}
				});
			}
		});
		ui.find('.invite-decline').on('click', function(){
			var btn = $(this);
			if(confirm('Are you sure you want to decline this invite?')){
				btn.attr('disabled','disabled');
				$.ajax({
					type: 'get',
					data: {'teamId':btn.data('teamid')},
					url: destiny.baseUrl + 'Fantasy/Challenge/Decline',
					success: function(data){
						btn.closest('tr').replaceWith('<tr><td>-</td></tr>');
					},
					error: function(){
						alert('An error occured.');
						btn.removeAttr('disabled');
					}
				});
			}
		});
		ui.find('.sent-invite-delete').on('click', function(){
			var btn = $(this);
			if(confirm('Are you sure you want to remove this invite?')){
				btn.attr('disabled','disabled');
				$.ajax({
					type: 'get',
					data: {'teamId':btn.data('teamid')},
					url: destiny.baseUrl + 'Fantasy/Challenge/Delete',
					success: function(data){
						btn.closest('tr').replaceWith('<tr><td>-</td></tr>');
					},
					error: function(){
						alert('An error occured.');
						btn.removeAttr('disabled');
					}
				});
			};
			return false;
		});
	});

	$('#challengerGrid').each(function(){
		var ui = $(this); 
		ui.find('.remove-challenger').on('click', function(){
			var btn = $(this);
			if(confirm('Are you sure you want to remove this challenger?')){
				$.ajax({
					type: 'get',
					data: {'teamId':btn.data('teamid')},
					url: destiny.baseUrl + 'Fantasy/Challenge/Delete',
					success: function(data){
						btn.closest('tr').replaceWith('<tr><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>');
					},
					error: function(){
						alert('An error occured.');
						btn.removeAttr('disabled');
					}
				});
			};
			$(this).data('teamid');
			return false;
		});
	});
});