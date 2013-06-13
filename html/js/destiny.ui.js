
// Document ready
$(function(){
	
	// Lastfm
	new DestinyFeedConsumer({
		url: destiny.urls.lastfm,
		polling: destiny.polling.lastfm,
		ifModified: true,
		start: true,
		ui: '#stream-lastfm',
		success: function(data, textStatus){
			if(textStatus == 'notmodified') return;
			if(data != null && typeof data['recenttracks'] == 'object' && data.recenttracks.track.length > 0){
				var entries = $('#stream-lastfm .entries:first').empty();
				for(var i in data.recenttracks.track){
					if(i == 3){ break; };
					var track = data.recenttracks.track[i];
					var entry = $(
							'<div class="media">'+ 
								'<a class="pull-left cover-image" href="'+ track.url +'">'+ 
									'<img class="media-object" src="'+destiny.cdn+'/img/64x64.gif" data-src="'+ track.image[1]['#text'] +'">'+ 
								'</a>'+ 
								'<div class="media-body">'+ 
									'<h4 class="media-heading trackname"><a title="'+ htmlEncode(track.name) +'" href="'+ track.url +'">'+ htmlEncode(track.name) +'</a></h4>'+ 
									'<div class="artist">'+ htmlEncode(track.artist['#text']) +'</div>'+ 
									'<div class="details">'+
										((i<3 && track.date_str != '') ? '<time class="pull-right" datetime="'+ track.date_str +'">'+ moment(track.date_str).fromNow() +'</time>' : '')+ 
										((i==0 && track.date_str == '') ? '<time class="pull-right" datetime="'+ track.date_str +'">now playing</time>' : '')+ 
										'<small class="album">'+ track.album['#text'] +'</small>'+ 
									'</div>'+ 
								'</div>'+ 
							'</div>'
					);
					entries.append(entry);
				};
				entries.loadImages();
			};
		}
	});
	
	// Twitter
	new DestinyFeedConsumer({
		url: destiny.urls.twitter,
		polling: destiny.polling.twitter,
		ifModified: true,
		start: true,
		ui: '#stream-twitter',
		success: function(tweets, textStatus){
			if(textStatus == 'notmodified') return;
			var entries = $('#stream-twitter .entries:first').empty();
			for(var i in tweets){
				if(i == 3){ break; };
				entries.append(
					'<div class="media">'+ 
						'<div class="media-body">'+ 
							'<div class="media-heading"><a target="_blank" href="'+ 'https://twitter.com/'+ tweets[i].user.screen_name +'/status/' + tweets[i].id_str +'"><i class="icon-share icon-white subtle"></i></a> '+ 
								tweets[i].html +
							'</div>'+ 
							'<time datetime="'+ tweets[i].created_at +'" pubdate>'+ moment(tweets[i].created_at).fromNow() +'</time>'+ 
						'</div>'+ 
					'</div>'
				);
			};
			entries.loadImages();
		}
	});
	
	// Youtube
	new DestinyFeedConsumer({
		url: destiny.urls.youtube,
		ifModified: true,
		start: true,
		ui: '#youtube',
		success: function(data, textStatus){
			if(textStatus == 'notmodified') return;
			var ui = $('#youtube .thumbnails:first').empty();
			ui.removeClass('thumbnails-loading');
			if(typeof data == 'object'){
				for(var i in data.items){
					var title = htmlEncode(data.items[i].snippet.title);
					ui.append(
						'<li>'+
							'<div class="thumbnail" rel="tooltip" title="'+ title + '">'+
								'<a href="'+ 'http://www.youtube.com/watch?v='+ data.items[i].snippet.resourceId.videoId +'">'+ 
									'<img alt="'+ title +'" src="'+destiny.cdn+'/img/320x240.gif" data-src="https://i.ytimg.com/vi/'+ data.items[i].snippet.resourceId.videoId +'/default.jpg" />'+
								'</a>'+
							'</div>'+
						'</li>'
					);
					ui.find('.thumbnail').tooltip({placement:'bottom'});
				};
				ui.loadImages();
			};
		}
	});
	
	// Past Broadcasts
	new DestinyFeedConsumer({
		url: destiny.urls.broadcasts,
		polling: destiny.polling.broadcasts,
		ifModified: true,
		start: true,
		ui: '#broadcasts',
		success: function(data, textStatus){
			if(textStatus == 'notmodified') return;
			var broadcasts = $('#broadcasts'),
				ui = broadcasts.find('.thumbnails:first').empty();
			ui.removeClass('thumbnails-loading');
			if(data != null && typeof data.videos != undefined){
				for(var i in data.videos){
					var time = moment(data.videos[i].recorded_at).fromNow();
					ui.append(
						'<li>'+
							'<div class="thumbnail" data-placement="bottom" rel="tooltip" title="'+ time +'">'+
								'<a href="'+ data.videos[i].url +'">'+ 
									'<img alt="'+ time +'" src="'+destiny.cdn+'/img/320x240.gif" data-src="'+ data.videos[i].preview +'">'+
								'</a>'+
							'</div>'+
						'</li>'
					);
				};
				ui.loadImages();
				ui.find('[rel="tooltip"]').tooltip();
			};
		}
	});
	
	// Stream details
	new DestinyFeedConsumer({
		url: destiny.urls.stream,
		polling: destiny.polling.stream,
		ifModified: true,
		ui: '#twitchpanel',
		success: function(data, textStatus){
			if(textStatus == 'notmodified') return;
			var ui = $('#twitchpanel .panelheader .game');
			if(data != null && data.stream != null){
				ui.html( '<i class="icon-time icon-white subtle"></i> <span>Started '+ moment(data.stream.channel.updated_at).fromNow() +'</span>' + ((data.stream.channel.delay > 0) ? ' - ' + parseInt((data.stream.channel.delay/60)) + 'm delay':'')).show();
				$('#twitchpanel').removeClass('offline').addClass('online');
			}else{
				try{
					ui.html( '<i class="icon-time icon-white subtle"></i> <span>Last broadcast ended '+ moment(data.lastbroadcast).fromNow() +'</span>').show();
				}catch(e){
					ui.html( '<i class="icon-time icon-white subtle"></i> <span>Broadcast has ended</span>').show();
				}
				$('#twitchpanel').removeClass('online').addClass('offline');
			}
		}
	});
	
	// Summoner details
	var summonerFeed = new DestinyFeedConsumer({
		url: destiny.urls.summonerstats,
		polling: destiny.polling.summonerstats,
		ifModified: true,
		start: false,
		ui: '#lolpanel',
		success: function(summoners, textStatus){
			if(textStatus == 'notmodified') return;
			var panel = $('#lolpanel');
			if(typeof summoners != 'object' || summoners == null || summoners.length == 0){
				panel.hide(); 
				return;
			};
			panel.find('.content:first').empty();
			for(var i=0; i<summoners.length; ++i) {
				var summoner = summoners[i],
					ui = $('<div id="summoner-'+ summoner.summonerId +'" class="summoner-stub clearfix" />');
				ui.append(
					'<div class="summoner-info pull-left">'+
						'<div class="summoner-info-stub pull-left">'+
							'<div class="summoner-icon">'+
								'<img src="'+destiny.cdn+'/img/64x64.gif" style="width:45px; height:45px;"'+ ((summoner['profileIconId'] != undefined) ? ' data-src="'+destiny.cdn+'/img/lol/summoner/profileIcon'+summoner.profileIconId+'.jpg"' : '' ) +'" />' +
							'</div>'+
							'<h3 class="summoner-title">'+ 
								htmlEncode(summoner.name) + ' <small><a title="LOLKING profile" href="http://www.lolking.net/summoner/'+ summoner.region.id +'/'+ summoner.summonerId +'">lolking.com</a></small> ' +
							'</h3>'+
							'<span class="summoner-region">'+
								summoner.region.label +
								((summoner['summonerLevel'] == undefined) ? '':
								' - Level '+ summoner.summonerLevel)+
								((summoner.league == null) ? '':
								' - Elo '+ summoner.league.approximateElo) +
							'</span>'+
						'</div>'+
					'</div>'
				);
				if(summoner.league == null){
					// Unknown
					ui.append(
						'<div class="summoner-rank-info unranked pull-right">'+
							'<div class="pull-left summon-rank-display">'+
								'<div class="summoner-rank unranked pull-left" style="">Unknown</div>' +
								'<div class="summoner-rank-thumbnail pull-left"><i title="Unknown" style="width:45px; height:45px; background: url('+destiny.cdn+'/img/lol/rank/unknown.png) no-repeat center center; background-size: 60px 60px;"></i></div>'+
							'</div>'+
						'</div>'
					);
				}else{
					// Ranked
					var miniSeries = (summoner.league['miniSeries'] != undefined) ? summoner.league.miniSeries : null;
					var position = summoner.league.position,
						positionOffset = position - summoner.league.previousDayLeaguePosition;
					ui.append(
						'<div class="summoner-rank-info ranked pull-right">'+
							((miniSeries == null)  ? '' : 
							'<div class="pull-left summon-rank-stats summon-mini-series">'+
								'<div><span style="color: #b19e00;">'+ miniSeries.target + '</span> game series</div>' +
								'<div><span style="color: #1a6f00;">'+ miniSeries.wins +'</span> / <span style="color: #8a1919;">'+ miniSeries.losses +'</span> Win Loss</div>' +
							'</div>') +
							'<div class="pull-left summon-rank-stats">'+
								'<div>' + htmlEncode(summoner.league.leagueName) + '</div>' +
								'<div><span data-placement="left" rel="tooltip" title="Previous day position '+ summoner.league.previousDayLeaguePosition +'">Position '+
									'<i class="icon-arrow-'+ ((positionOffset > 0) ? 'down':'up') +' icon-white"></i></span>' +									
									'<span data-placement="left" rel="tooltip" title="Out of '+ (summoner.league.totalEntries) +'" style="color: #'+ ((positionOffset > 0) ? '8a1919':'1a6f00') +';">' + (position) + '</span>'+
									((summoner.league.hotStreak == false) ? '' :
									' <i data-placement="left" rel="tooltip" class="icon-fire icon-white" title="HOOOOOTTT STTTRRREEEAAAAAKKKKKK!"></i> ') +
									((summoner.league.freshBlood == false) ? '' : 
									' <i data-placement="left" rel="tooltip" class="icon-tint icon-white" title="Fresh Meat!"></i> ') +
								'</div>'+
							'</div>'+
							'<div class="pull-left summon-rank-stats">'+
								'<div><span style="color: #b19e00;">' + summoner.league.leaguePoints + '</span> League Points</div>' +
								'<div><span style="color: #1a6f00;">' + summoner.league.wins +'</span> / <span style="color: #8a1919;">'+ summoner.league.losses + '</span> Win Loss</div>' +
							'</div>'+
							'<div class="pull-left summon-rank-display">'+
								'<div class="summoner-rank ranked pull-left"><span style="text-transform: capitalize;">'+ summoner.league.tier.toLowerCase() +'</span> '+ summoner.league.rank +'</div>' +
								'<div class="summoner-rank-thumbnail pull-left"><i data-placement="left" rel="tooltip" title="'+ summoner.league.tier +' '+ summoner.league.rank +'" style="width:45px; height:45px; background: url('+destiny.cdn+'/img/lol/rank/' + summoner.league.tier.toLowerCase() + '_' + summoner.league.rankInt + '.png) no-repeat center center; background-size: 60px 60px;"></i></div>'+
							'</div>'+
						'</div>'
					);
				};
				panel.find('.content:first').append(ui);
			};
			panel.loadImages();
			panel.find('[rel="tooltip"]').tooltip();
		}
	});

	// Ingame
	(function(){
		var ingameFeed = null, activeGameUi = $('#activeIngame').hide();
		if(activeGameUi.get(0) == null){
			return false;
		};
		var getChampPick = function(game, summoner){
			for(var n in game.gameSummonerSelections){
				var selection = game.gameSummonerSelections[n];
				if(selection.summonerId == summoner.summonerId){
					return selection;
				}
			}
			return null;
		};
		var buildTeam = function(game, champions){
			var str = '';
			for(var n in champions){
				var champ = champions[n], pick = getChampPick(game, champ);
				str +=
				'<div class="game-champion clearfix">'+
					'<div class="summoner-spells">'+
						'<span class="spell spell1"><img data-src="'+ destiny.cdn +'/img/lol/spell/'+ pick.spell1Id +'.png" src="'+ destiny.cdn +'/img/64x64.gif" /></span>'+
						'<span class="spell spell2"><img data-src="'+ destiny.cdn +'/img/lol/spell/'+ pick.spell2Id +'.png" src="'+ destiny.cdn +'/img/64x64.gif" /></span>'+
					'</div>'+
					'<div class="thumbnail">'+
						'<a href="http://www.lolking.net/summoner/'+ champ.region.toLowerCase() +'/'+ champ.summonerId +'"><img title="'+ htmlEncode(pick.championName) +'" src="'+ destiny.cdn +'/img/320x320.gif" data-src="'+ getChampIcon(pick.championName) +'" /></a>'+
					'</div>'+
					'<div class="summoner-detail">'+
						'<div class="name"><a class="subtle-link" href="http://www.lolking.net/summoner/'+ champ.region.toLowerCase() +'/'+ champ.summonerId +'">'+ htmlEncode(champ.name) +'</a></div>'+
					'</div>'+
				'</div>';
			};
			return str;
		};
		var buildBans = function(bans){
			var str = '';
			if(bans.length > 0){
				str +=
				'<div class="game-team-bans clearfix">'+
					'<div>';
				for(var n in bans){
					var ban = bans[n];
					str += '<div class="champion champion-banned"><img title="'+ban.name+'" src="'+ destiny.cdn +'/img/320x320.gif" data-src="'+ getChampIcon(ban.name) +'" /></div>';
				}
				str +=
					'</div>'+
				'</div>';
			};
			return str;
		};
		ingameFeed = new DestinyFeedConsumer({
			url: destiny.urls.ingame,
			polling: destiny.polling.ingame,
			ifModified: true,
			start: true,
			data: {},
			success: function(game, textStatus){
				// this doesnt actually support modified yet
				if(textStatus == 'notmodified') return;
				// Sends the gameId on the next request
				if(game != null && game['gameId'] != null){
					ingameFeed.args.data.gameId = game.gameId;
					var ui = $(
						'<div class="game ingame clearfix" data-gameId="'+ game.gameId +'">' +
							'<div class="clearfix">' +
								'<div class="game-champions clearfix">' +
									'<div class="game-team game-team1 pull-left" style="width:50%;">' +
										buildTeam(game, game.gameTeams['100']) +
										buildBans(game.gameBannedChampions['100']) +
									'</div>' +
									'<div class="game-team game-team2 pull-right" style="width:50%;">' +
										buildTeam(game, game.gameTeams['200']) +
										buildBans(game.gameBannedChampions['200']) +
									'</div>' +
								'</div>' +
								'<div class="gameVersus">Vs</div>' +
								'<div class="gameLegend">Game in-progress</div>' +
								'<div class="gameBans">Bans</div>' +
							'</div>' +
						'</div>'
					);
					activeGameUi.html(ui);
					activeGameUi.loadImages();
					activeGameUi.fadeIn(1000);
				}else{
					activeGameUi.hide();
				};
			}
		});
	})();
	
	// Private ads / rotation
	var pads = $('.private-ads .private-ad'), adRotateIndex = pads.length-1;
	setInterval(function(){
		$(pads[adRotateIndex]).fadeOut(500, function(){
			$(this).hide().removeClass('active');
		});
		if(adRotateIndex < pads.length-1) adRotateIndex++; else adRotateIndex = 0;
		$(pads[adRotateIndex]).hide().addClass('active').fadeIn(500);
	}, destiny.polling.adsRotate * 1000);

	
	// Check if the ad has been blocked after X seconds
	var gad = $('#google-ad');
	setTimeout(function(){
		if(gad.css('display') == 'none' || parseInt(gad.height()) <= 0){
			gad.before('<div id="adblocker-message"><a>Add blocker</a><p>Please consider turning adblocker off for this website.</p></div>');
		}
	}, 8000);
	
	// Twitch Connect button
	$('a[rel="twitchlogin"],button[rel="twitchlogin"]').on('click', function(){
		var url = 'https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id='+$(this).data('client-id')+'&redirect_uri='+$(this).data('redirect-uri')+'&scope='+$(this).data('request-perms');
		window.location.href = url;
		return false;
	});
	
	// Navigation
	$('.nav a[rel="signout"]').click(function(){
		if(confirm('Are you sure?')){
			window.location.href = destiny.baseUrl + 'TwitchLogout';
		};
	});

	// Calendar
	(function(){
		var timezone = moment().format('ZZ'),
			calendarFrame = $('#scheduleCalendar'),
			iframe = calendarFrame.find('iframe'),
			tzNote = $('#scheduleCalendarTimezone'),
			timezoneSelect = tzNote.find('select'),
			timezoneBtn = tzNote.find('button.timezone');
		var loadGoogleCalendar = function(){
			iframe.attr('src', iframe.data('src') + '&ctz=' + encodeURIComponent(timezone));
			timezoneBtn.html(timezone);
		};
		timezoneBtn.on('click', function(){
			$('#scheduleCalendarForm').toggle('hide');
		});
		timezoneSelect.on('change', function(){
			timezone = timezoneSelect.val();
			loadGoogleCalendar();
			return false;
		});
		loadGoogleCalendar();
	})();
	
	// The check games button
	$(function(){
		var btn = $('button[href="#checkRecentGames"]'), html = btn.html(), lastCheck = new Date(btn.data('lastcheck'));
		btn.on('click', function(){
			if(btn.hasClass('busy')){
				return false;
			}
			btn.addClass('busy');
			btn.html('<i class="icon-time"></i> Checking for new games....');
			window.setTimeout(function(){
				$.ajax({
					ifModified: true,
					url: destiny.baseUrl + 'Fantasy/Recentgames.json',
					success: function(data, textStatus){
						var aggregateDate = (data != null) ? new Date(data.date) : null;
						if(textStatus == 'notmodified' || !aggregateDate || aggregateDate <= lastCheck){
							btn.addClass('btn-danger');
							btn.html('<i class="icon-remove icon-white"></i> No new games found');
							window.setTimeout(function(){
								btn.html(html).removeClass('btn-danger busy');
							}, 3000);
						}else{
							btn.addClass('btn-success').html('<i class="icon-thumbs-up icon-white"></i> New game was found');
							window.setTimeout(function(){window.location.reload(false)}, 1000);
						};
					}
				});
			}, 1000);
			return false;
		});
		
	});
	
	// Subscriptions
	$(function(){
		var subscriptionsUi = $('#subscriptions');
		subscriptionsUi.on('click', '.subscription:not(.active)', function(e){
			subscriptionsUi.find('.subscription.active').each(function(){
				$(this).removeClass('active');
			});
			$(this).find('input[name="subscription"]').prop('checked', true);
			$(this).find('.payment-options input[type="radio"]:first').prop('checked', true);
			$(this).addClass('active');
		});
	});
	
	// Add collapses
	$(".collapse").collapse();
	
	// Tabs selector - dont know why I need this
	if (location.hash !== '') $('a[href="' + location.hash + '"]').tab('show');

	// Set the top nav selection
	$('.navbar a[rel="'+$('body').attr('id')+'"]').closest('li').addClass('active');
	
	// Change time on selected elements
	$('time[data-moment="true"]').each(function(){
		var ui = $(this),
			datetime = ui.attr('datetime') || ui.text(),
			str = moment(datetime).format('MMMM Do, h:mm:ss a');
		ui.html(str);
	});
	
	// Lazy load images
	$(this).loadImages();
	$(this).find('[rel="tooltip"]').tooltip();
});