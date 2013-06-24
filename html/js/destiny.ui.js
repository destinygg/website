
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
			var entries = $('#stream-lastfm .entries:first').empty();
			if(data != null && typeof data['recenttracks'] == 'object' && data.recenttracks.track.length > 0){
				for(var i in data.recenttracks.track){
					if(i == 3){ break; };
					var track = data.recenttracks.track[i];
					var entry = $(
							'<div class="media">'+ 
								'<a class="pull-left cover-image" href="'+ track.url +'">'+ 
									'<img class="media-object" src="'+destiny.cdn+'/img/64x64.gif" data-src="'+ track.image[1]['#text'] +'">'+ 
								'</a>'+ 
								'<div class="media-body">'+ 
									'<div class="media-heading trackname"><a title="'+ htmlEncode(track.name) +'" href="'+ track.url +'">'+ htmlEncode(track.name) +'</a></div>'+ 
									'<div class="artist">'+ htmlEncode(track.artist['#text']) +'</div>'+ 
									'<div class="details">'+
										((i<3 && track.date_str != '') ? '<time class="pull-right" datetime="'+ track.date_str +'">'+ moment(track.date_str).fromNow() +'</time>' : '')+ 
										((i==0 && track.date_str == '') ? '<time class="pull-right" datetime="'+ track.date_str +'">now playing</time>' : '')+ 
										'<small class="album subtle">'+ track.album['#text'] +'</small>'+ 
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
	new DestinyFeedConsumer({
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

	(function(){
		
		// Ingame
		var currentIngameId = $('#activeGame').data('gameid');
		new DestinyFeedConsumer({
			url: destiny.urls.ingame,
			polling: 30,
			ifModified: true,
			start: false,
			ui: '#newInGameAlert',
			data: {},
			success: function(game, textStatus){
				// this doesnt actually support modified yet
				if(textStatus == 'notmodified') return;
				if(game != null && game['gameId'] != null){
					if(game['gameId'] != currentIngameId){
						$('#newInGameAlert:hidden').fadeIn();
						$('#activeGame:visible').fadeOut();
					}else{
						$('#newInGameAlert:visible').fadeOut();
						$('#activeGame:hidden').fadeIn();
					}
				}else{
					$('#activeGame:visible').fadeOut();
				}
			}
		});
		
		// Check recent games
		var lastChecked =  new Date(); // set on the initial page load
		new DestinyFeedConsumer({
			url: destiny.baseUrl + 'Fantasy/Recentgames.json',
			polling: 30,
			ifModified: true,
			start: false,
			ui: '#newGameAlert',
			success: function(data, textStatus){
				var aggregateDate = (data != null) ? new Date(data.date) : null;
				if(textStatus == 'notmodified' || !aggregateDate || aggregateDate <= lastChecked){
					$('#newGameAlert:visible').fadeOut();
				}else{
					$('#newGameAlert:hidden').fadeIn();
				}
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
	
	// Navigation
	$('.nav a[rel="signout"]').click(function(){
		if(confirm('Are you sure?')){
			window.location.href = destiny.baseUrl + 'Logout';
		};
	});

	// Calendar
	(function(){
		var timezone = moment().format('ZZ'),
			calendarFrame = $('#scheduleCalendar'),
			iframe = calendarFrame.find('iframe'),
			tzNote = $('#scheduleCalendarTimezone'),
			timezoneSelect = tzNote.find('select'),
			timezoneBtn = tzNote.find('button.change-timezone'),
			timezoneLbl = tzNote.find('span.timezone');
		var loadGoogleCalendar = function(){
			iframe.attr('src', iframe.data('src') + '&ctz=' + encodeURIComponent(timezone));
			timezoneLbl.html(timezone);
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
	
	// Subscriptions
	(function(){
		var subscriptionsUi = $('#subscriptions');
		subscriptionsUi.on('click', '.subscription:not(.active)', function(e){
			subscriptionsUi.find('.subscription.active').each(function(){
				$(this).removeClass('active');
			});
			$(this).find('input[name="subscription"]').prop('checked', true);
			$(this).find('.payment-options input[type="radio"]:first').prop('checked', true);
			$(this).addClass('active');
		});
	})();
	
	// Theater
	(function(){
		$('body#bigscreen').each(function(){
			
			var offset = 81;
			var pc = $('.page-content');
			var _resize = function(){
				var bh = $('body').height(); 
				if(bh < 550){
					bh = 550;
				}
				pc.height( bh - offset );
				pc.find('#twitch-chat-wrap .twitch-element').height( pc.height()-20 );
				pc.find('#twitch-stream-wrap .twitch-element').height( pc.height() - 20 - 30 );
			};
			$(window).on('resize', _resize);
			_resize();

			// Stream details
			new DestinyFeedConsumer({
				url: destiny.urls.stream,
				polling: destiny.polling.stream,
				ifModified: true,
				success: function(data, textStatus){
					if(textStatus == 'notmodified') return;
					var ui = $('#twitch-stream-wrap .panelheader .game');
					if(data != null && data.stream != null){
						ui.html( '<i class="icon-time icon-white subtle"></i> <span>Started '+ moment(data.stream.channel.updated_at).fromNow() +'</span>' + ((data.stream.channel.delay > 0) ? ' - ' + parseInt((data.stream.channel.delay/60)) + 'm delay':'')).show();
					}else{
						try{
							ui.html( '<i class="icon-time icon-white subtle"></i> <span>Last broadcast ended '+ moment(data.lastbroadcast).fromNow() +'</span>').show();
						}catch(e){
							ui.html( '<i class="icon-time icon-white subtle"></i> <span>Broadcast has ended</span>').show();
						}
					}
				}
			});
		});
	})();
	
	// refresh-form captcha
	$('form .refresh-captcha').click(function(){
		var img = $(this).prev(), src = img.attr('src');
		img.removeAttr('str').attr('src', src);
		return false;
	});
	
	// Add collapses
	$(".collapse").collapse();
	
	// Tabs selector - dont know why I need this
	if (location.hash !== '') $('a[href="' + location.hash + '"]').tab('show');

	// Set the top nav selection
	$('body').each(function(){
		$('.navbar a[rel="'+$('body').attr('id')+'"]').closest('li').addClass('active');
		$('.navbar a[rel="'+$('body').attr('class')+'"]').closest('li').addClass('active');
	});
	
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