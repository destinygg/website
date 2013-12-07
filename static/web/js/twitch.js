$(function(){

	var twitch = $('#twitchpanel'), 
		twitchElements = $('#twitch-elements'),
		chat = twitchElements.find('#chat-embed'), 
		player = twitchElements.find('#player-embed'),
		popoutChatBtn = twitch.find('#popoutchat'),
		popoutVideoBtn = twitch.find('#popoutvideo');
	
	// Refs for standard and bigscreen mode
	var playerWrap = $("div.twitch-element-wrap"),
		fullscreenBtn = playerWrap.find('div.twitch-fsbtn'),
		playerOverlays = playerWrap.find('div.twitch-overlay');

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
	
	/**
	 * Iterate over browser variants of the given fullscreen API function string,
	 * and if found, call it on the given element.
	 */	
	var fullscreenFn = function(fn, elem) {
		var agents = ["webkit", "moz", "ms", "o", ""];
		for (var i = agents.length - 1; i >= 0; i--) {
			var agent  = agents[i],
			    fullFn = null;
			
			if (!agent) // if no agent the function starts with a lower case letter
				fullFn = fn.substr(0,1).toLowerCase() + fn.substr(1);
			else // just preprend the agent to the function
				fullFn = agent + fn;
			
			if (typeof elem[fullFn] != "function")
				continue;
			
			elem[fullFn]();
			break;
		};
	};
	
	// Toggles player fullscreen using the global fullscreen flag
	var toggleFullscreen = function(e) {
		e.preventDefault();
		if(document.fullscreen || document.mozFullScreen || document.webkitIsFullScreen){
			fullscreenFn("CancelFullScreen", document);
		} else {
			fullscreenFn("RequestFullScreen", playerWrap.get(0));
		}
	}
	
	//Bind clicks on overlay items that prevent accidental redirects to twitch
	fullscreenBtn.click(toggleFullscreen);
	playerOverlays.dblclick(toggleFullscreen);

});