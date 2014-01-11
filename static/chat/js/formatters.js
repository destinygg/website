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
				$.inArray('flair3', user.features) !== -1 ||
				$.inArray('flair1', user.features) !== -1 ||
				$.inArray('admin', user.features) !== -1 ||
				$.inArray('moderator', user.features) !== -1 
			)
				str = '<span class="greentext">'+str+'</span>';
		}
		return str;
	}
	
	// Emote line formatter
	destiny.fn.EmoteFormatter = function(chat){
		this.emoteregex = new RegExp('(^|\\s)('+chat.emoticons.join('|')+')($|\\s)');
		this.gemoteregex = new RegExp('(^|\\s)('+chat.emoticons.join('|')+')($|\\s)', 'gm');
		return this;
	};
	destiny.fn.EmoteFormatter.prototype.format = function(str, user){
		var emoteregex = (user && ((user.features || []).length > 0)) ? this.gemoteregex:this.emoteregex;
		return str.replace(emoteregex, '$1<div title="$2" class="chat-emote chat-emote-$2"></div>$3');
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
		    tlds      = "(?:MUSEUM|TRAVEL|AERO|ARPA|ASIA|COOP|INFO|JOBS|MOBI|NAME|POST|BIZ|CAT|COM|EDU|GOV|INT|MIL|NET|ORG|PRO|TEL|XXX|AC|AD|AE|AF|AG|AI|AL|AM|AN|AO|AQ|AR|AS|AT|AU|AW|AX|AZ|BA|BB|BD|BE|BF|BG|BH|BI|BJ|BM|BN|BO|BR|BS|BT|BV|BW|BY|BZ|CA|CC|CD|CF|CG|CH|CI|CK|CL|CM|CN|CO|CR|CU|CV|CW|CX|CY|CZ|DE|DJ|DK|DM|DO|DZ|EC|EE|EG|ER|ES|ET|EU|FI|FJ|FK|FM|FO|FR|GA|GB|GD|GE|GF|GG|GH|GI|GL|GM|GN|GP|GQ|GR|GS|GT|GU|GW|GY|HK|HM|HN|HR|HT|HU|ID|IE|IL|IM|IN|IO|IQ|IR|IS|IT|JE|JM|JO|JP|KE|KG|KH|KI|KM|KN|KP|KR|KW|KY|KZ|LA|LB|LC|LI|LK|LR|LS|LT|LU|LV|LY|MA|MC|MD|ME|MG|MH|MK|ML|MM|MN|MO|MP|MQ|MR|MS|MT|MU|MV|MW|MX|MY|MZ|NA|NC|NE|NF|NG|NI|NL|NO|NP|NR|NU|NZ|OM|PA|PE|PF|PG|PH|PK|PL|PM|PN|PR|PS|PT|PW|PY|QA|RE|RO|RS|RU|RW|SA|SB|SC|SD|SE|SG|SH|SI|SJ|SK|SL|SM|SN|SO|SR|ST|SU|SV|SX|SY|SZ|TC|TD|TF|TG|TH|TJ|TK|TL|TM|TN|TO|TP|TR|TT|TV|TW|TZ|UA|UG|UK|US|UY|UZ|VA|VC|VE|VG|VI|VN|VU|WF|WS|YE|YT|ZA|ZM|ZW)(?!\\w)",
		    ipaddr    = "(?:(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])\\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])";
		this.linkregex = new RegExp(
			"(((?:https?|ftp):\\/\\/)?" + //begin URL group, match protocol (if any)
			"(?:[\\w]+(?::" + chars_reg + ")?@)?" + //match basic auth user/password prefix
			"(?:" + ipaddr + "|(?:"+ //match an ip address or domain
			"(?:[\\w-]+\\.)+" + //match subdomains and domain
			tlds + //match valid+accepted TLDs, we don't care about punycode
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
