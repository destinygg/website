$(function(){

	var twitch = $('#twitchpanel'), 
		twitchElements = $('#twitch-elements'),
		chat = twitchElements.find('#twitch-chat'), 
		player = twitchElements.find('#twitch-player'),
		popoutChatBtn = twitch.find('#popoutchat'),
		popoutVideoBtn = twitch.find('#popoutvideo');

	var twitchChat = {
		popup: null,
		toggle: function(){
			if(this.popup == null) this.popOut(); else this.popIn();
		},
		popOut: function(){
			popoutChatBtn.attr('title', 'Re-embed chat').addClass('btn-down');
			if(twitchElements.children().length <= 1){
				twitch.removeClass('split-view single-view').addClass('no-view');
			}else{
				twitch.removeClass('split-view').addClass('single-view');
			}
			chat.detach();
			this.popup = window.open(twitch.data('chat-embed'),'_blank','height=500,width=420,scrollbars=0,toolbar=0,location=0,status=no,menubar=0,resizable=0,dependent=0');
		},
		popIn: function(){
			popoutChatBtn.attr('title', 'Pop-out chat').removeClass('btn-down');
			if(twitchElements.children().length <= 0){
				twitch.removeClass('split-view').addClass('single-view');
			}else{
				twitch.addClass('split-view').removeClass('single-view');
			}
			if(this.popup != null){
				this.popup.close();
				this.popup = null;
			}
			chat.appendTo(twitchElements);
		}
	};

	var twitchVideo = {
		popup: null,
		toggle: function(){
			if(this.popup == null) this.popOut(); else this.popIn();
		},
		popOut: function(){
			popoutVideoBtn.attr('title', 'Re-embed video').addClass('btn-down');
			if(twitchElements.children().length <= 1){
				twitch.removeClass('split-view single-view').addClass('no-view');
			}else{
				twitch.removeClass('split-view').addClass('single-view');
			}
			player.detach();
			this.popup = window.open(twitch.data('video-embed'),'_blank','height=420,width=720,scrollbars=0,toolbar=0,location=0,status=no,menubar=0,resizable=1,dependent=0');
		},
		popIn: function(){
			popoutVideoBtn.attr('title', 'Pop-out video').removeClass('btn-down');
			if(twitchElements.children().length <= 0){
				twitch.removeClass('split-view').addClass('single-view');
			}else{
				twitch.addClass('split-view').removeClass('single-view');
			}
			if(this.popup != null){
				this.popup.close();
				this.popup = null;
			}
			player.prependTo(twitchElements);
		}
		
	};

	popoutChatBtn.on('click', function(){ twitchChat.toggle(); });
	popoutVideoBtn.on('click', function(){ twitchVideo.toggle(); });

	// Periodically check if the stream is offline, ad show ads.
	var offlineAdvert = {
			
		ui: $('<div id="player-ads" class="clearfix" />'),
		polling: 5000,
		intervalId: null,
		
		init: function(){
			var self = this;
			self.ui.html(
				'<div id="player-ads-video">'+
					'<p>The stream is offline. This screen will automatically <a href="#" id="close-player-ad" title="Close">close</a> when stream is live.<br />Click <a title="Google Calendar" href="/schedule">here</a> to see when he\'ll be streaming next</p>'+
					'<iframe id="youtube-embed" src="http://www.youtube.com/embed/?listType=user_uploads&list='+ twitch.data('youtube-user') +'&showinfo=1" frameborder="0" allowfullscreen></iframe>'+
				'</div>'
			);
			self.ui.on('click', '#close-player-ad', function(){
				self.destroy();
				return false;
			});
			self.ui.find('.thumbnail').tooltip({placement:'left'});
			player.append(self.ui);
			self.ui.loadImages();
		},

		initTimer: function(){
			var self = this;
			self.intervalId = setInterval(function(){
				if(twitch.hasClass('offline') && false == player.hasClass('defocus')){
					player.addClass('defocus');
					self.ui.detach();
					self.init();
				}else if(twitch.hasClass('online') && player.hasClass('defocus')){
					clearInterval(self.intervalId);
					player.removeClass('defocus');
					self.destroy();
				};
			}, 5000);
		},

		destroy: function(){
			var self = this;
			clearInterval(self.intervalId);
			player.removeClass('defocus');
			self.ui.remove();
		}
			
	};

	offlineAdvert.initTimer();

});