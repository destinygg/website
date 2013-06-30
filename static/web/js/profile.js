$(function(){
	$('a[rel="resetteam"]').click(function(){
		if(confirm('This will remove all champions, reset scores, transfers and credits as well as remove all purchases?\r\nThis cannot be undone. Are you sure?')){
			if(confirm('Real talk now... are you sure?')){
				$.ajax({
					type: 'POST',
					async: false,
					url: destiny.baseUrl + 'fantasy/team/reset',
					complete: function(){ window.location.reload(); }
				});
			}
		};
		return false;
	});
});