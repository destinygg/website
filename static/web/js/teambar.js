$(function(){
	// Logged in fantasy team bar
	$('.fantasy-team-bar').each(function(){

		var fantasyTeamBar = $(this),
			championUi = fantasyTeamBar.find('.team-champions');
		
		var updateTeamAndChampions = function(team, champions){
			fantasyTeamBar.removeAttr('data-team');
			fantasyTeamBar.data('team', team);
			fantasyTeamBar.find('.team-stat.scoreValue .stat-value').text(team.scoreValue);
			fantasyTeamBar.find('.team-stat.credits .stat-value').text( Math.floor(team.credits) );
			fantasyTeamBar.find('.team-stat.transfersRemaining .stat-value').text(team.transfersRemaining);
			if(champions != undefined){
				championUi.find('.champion-slot').each(function(index){
					setupChampionSlot($(this), champions[index]);
				});
			};
		};
		
		var setupChampionSlot = function(slot, champion){
			var img = slot.find('.thumbnail img');
			if(champion != undefined){
				slot.data('champion', champion);
				slot.removeAttr('data-champion');
				slot.removeClass('champion-slot-full champion-slot-empty champion-illegal champion-free');
				slot.addClass('champion-slot-full');
				slot.addClass((champion.unlocked == 1) ? 'champion-unlocked': ((champion.championFree == '1') ? 'champion-free':'champion-illegal'));
				slot.attr('title', champion.championName);
				img.attr('alt', champion.championName);
				img.attr('src', getChampIcon(champion.championName));
			}else{
				img.attr('alt', '');
				img.attr('src', destiny.cdn + '/web/img/320x320.gif');
				slot.removeClass('champion-slot-full champion-slot-empty champion-illegal champion-free');
				slot.addClass('champion-slot-empty');
				slot.attr('title', 'None');
			}
		};
		
		var getChampions = function(){
			var champions = [];
			championUi.find('.champion-slot-full').each(function(){
				champions.push($(this).data('champion'));
			});
			return champions;
		};
		
		var getTeam = function(){
			return fantasyTeamBar.data('team');
		};
		
		var showTeamMaker = function(){
			if(championUi.hasClass('disabled')) return;
			DestinyTMI.show({
				team: getTeam(),
				champions: getChampions(),
				onsave: updateTeamAndChampions,
				onshow: function(){
					DestinyTMI.lockedState = true;
					DestinyTMI.setProgress('<span class="busy"><i class="icon-time icon-white subtle"></i> Loading your champions ...</span>');
					new DestinyFeedConsumer({
						url: destiny.baseUrl + 'Fantasy/Team/Champions.json',
						success: function(teamChampionResponse, textStatus){
							if(teamChampionResponse != null && teamChampionResponse.success == true){
								DestinyTMI.lockedState = false;
								DestinyTMI.setUnlockedChampions(teamChampionResponse.data);
								DestinyTMI.hideProgress();
								DestinyTMI.ui.loadImages();
								DestinyTMI.positionTeamMaker();
							}
						}
					});
				},
				onconfirm: function(){
					var champions = this.getChampionIds();
					// Check team
					if(champions.length > parseInt(this.settings.maxChampions)){
						throw 'At most '+this.settings.maxChampions+' champions required';
					}
					if(champions.length < parseInt(this.settings.minChampions)){
						throw 'At least '+this.settings.minChampions+' champions required';
					}
					// End Check
					return true;
				}
			});
			
			
			return false;
		};
		
		var manageTeamEl = fantasyTeamBar.find('.team-champions');
		manageTeamEl.find('.team-champions-slots').on('click', function(){
			$(this).popover('hide');
			showTeamMaker();
			return false;
		})
		if(getChampions().length <= 0){
			$(window).on('load', function(){
				manageTeamEl.find('.team-champions-slots').popover({
					placement: 'left',
					title:'You have no champions!',
					trigger:'manual',
					html: true,
					content:'Click here to add champions to your team.'
				}).popover('show');
			});
		};
		
		new DestinyFeedConsumer({
			url: destiny.baseUrl + 'fantasy/team.json',
			polling: 60,
			ui: '.fantasy-team-bar',
			ifModified: true,
			start: false,
			data: {teamId: getTeam().teamId},
			success: function(team, textStatus){
				if(textStatus == 'notmodified') return;
				if(team != null){
					updateTeamAndChampions(team, team.champions);
				}
			}
		});
		
	});
	
});