(function($){
	
	// Chat message formatters
	// must have two methods, a constructor and a .format(message, user) method
	
	// Green Text formatter
	destiny.fn.GreenTextFormatter = function(chat){
		return this;
	}
	destiny.fn.GreenTextFormatter.prototype.format = function(str, user){


		if(str.indexOf("&gt;") === 0){
            if(
                $.inArray(destiny.UserFeatures.SUBSCRIBERT3, user.features) > -1 ||
                $.inArray(destiny.UserFeatures.SUBSCRIBERT4, user.features) > -1 ||
                $.inArray(destiny.UserFeatures.SUBSCRIBERT2, user.features) > -1 ||
                $.inArray(destiny.UserFeatures.ADMIN, user.features) > -1 ||
                $.inArray(destiny.UserFeatures.MODERATOR, user.features) > -1 
            )
				str = '<span class="greentext">'+str+'</span>';
		}
		return str;
	}
	
	// Emote line formatter
	destiny.fn.EmoteFormatter = function(chat){
		this.emoteregex = new RegExp('(^|[\\s,\\.\\?!])('+chat.emoticons.join('|')+')(?=$|[\\s,\\.\\?!])');
		this.gemoteregex = new RegExp('(^|[\\s,\\.\\?!])('+chat.emoticons.join('|')+')(?=$|[\\s,\\.\\?!])', 'gm');
		return this;
	};
	destiny.fn.EmoteFormatter.prototype.format = function(str, user){
		var emoteregex = (user && ((user.features || []).length > 0)) ? this.gemoteregex:this.emoteregex;
		return str.replace(emoteregex, '$1<div title="$2" class="chat-emote chat-emote-$2"></div>');
	};
	
	// URL line formatter
	destiny.fn.UrlFormatter = function(chat){
		/* chars_reg - chars allowed in basic auth / path parameters
		 * path_reg - matches valid path params, including matching non-nested parentheses
		 * tlds - valid TLDs sorted first by length then alphabetically 
		 * ipregex - matches IP + port in place of url host
		 * */
		var chars_reg = "[\\w!\"#$%&'*+,-./:;<=>?@\\\\^`|~]*",
		    path_reg  = "(?:" + chars_reg + "(?:\\(" + chars_reg + "\\))?" + "(?:\\[" + chars_reg + "\\])?" + "(?:\\{" + chars_reg + "\\})?" +  ")*",
		    ipaddr    = "(?:(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])\\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])";
		this.linkregex = new RegExp(
			"(((?:https?|ftp):\\/\\/)?" + //begin URL group, match protocol (if any)
			"(?:[\\w]+(?::" + chars_reg + ")?@)?" + //match basic auth user/password prefix
			"(?:" + ipaddr + "|(?:"+ //match an ip address or domain
			"(?:[\\w-]+\\.)+" + //match subdomains and domain
			destiny.tlds + //match valid+accepted TLDs
			"))(?::[0-9]{1,5})?" + //match optional port (16-bit)
			"(?:\\/" + path_reg + ")?)" //match path and query, END URL group
			,"gi"
		);
		return this;
	}
	destiny.fn.UrlFormatter.prototype.encodeUrl = function(url){
		return url.replace(/["']/g, function(c){
			var htmlencmap = { // anything else gets already encoded
				"'": "&#39;",
				'"': "&quot;",
			};
			return htmlencmap[c];
		});
	};
	destiny.fn.UrlFormatter.prototype.format = function(str, user){
		if (!str) return;
		var nsfw      = (/\b(?:NSFW|NSFL|SPOILER)\b/i.test(str)),
		    css       = [],
		    formatter = this;
		
		css.push('externallink');
		if(nsfw)
			css.push('nsfw-link');
		
		return str.replace(this.linkregex, function(match, url, scheme){
			scheme = scheme ? '' : 'http://';
			var encodedUrl = formatter.encodeUrl(url);
			return match.replace(url, '<a target="_blank" class="'+ css.join(' ') +'" href="' + scheme + encodedUrl + '" rel="nofollow">' + encodedUrl + '</a>');
		});
	};	
	
})(jQuery);
