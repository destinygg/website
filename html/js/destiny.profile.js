
$(function(){
	
	$('a[rel="resetteam"]').click(function(){
		if(confirm('This will remove all champions, reset scores, transfers and credits as well as remove all purchases?\r\nThis cannot be undone. Are you sure?')){
			if(confirm('Real talk now... are you sure?')){
				$.ajax({
					type: 'POST',
					async: false,
					url: destiny.baseUrl + 'Fantasy/Team/Reset',
					complete: function(){ window.location.reload(); }
				});
			}
		};
		return false;
	});

	$('form#profileSaveForm').on('submit', function(){
		var submits = $(this).find('button[type="submit"]');
		submits.html('Updating...').attr('disabled', 'disabled');
		$.ajax({
			type: 'POST',
			async: false,
			data: $(this).serialize(),
			url: destiny.baseUrl + 'Profile/Save',
			complete: function(){
				window.setTimeout(function(){
					submits.html('Save changes').removeAttr('disabled');
				}, 1000);
			}
		});
		return false;
	});
});