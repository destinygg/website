(function($){
	
	// Chat message formatters
	// must have two methods, a constructor and a .format(message, user) method
	
	// Emote line formatter
	destiny.fn.EmoteFormatter = function(chat){
		this.emoteregex = new RegExp('\\b('+chat.emoticons.join('|')+')\\b');
		this.gemoteregex = new RegExp('\\b('+chat.emoticons.join('|')+')\\b', 'gm');
		return this;
	};
	destiny.fn.EmoteFormatter.prototype.format = function(str, user){
		var emoteregex = (user.features || []).length > 0? this.gemoteregex:this.emoteregex;
		return str.replace(emoteregex, '<div title="$1" class="chat-emote chat-emote-$1"></div>');
	};
	
	// URL line formatter
	destiny.fn.UrlFormatter = function(chat){
		var urlchars  = "\\w!\"#$%&'*+,-./:;<=>?@\\\\^`|~", //chars allowed in url path+auth params
		    tlds      = "(?:AC|AD|AE|AERO|AF|AG|AI|AL|AM|AN|AO|AQ|AR|ARPA|AS|ASIA|AT|AU|AW|AX|AZ|BA|BB|BD|BE|BF|BG|BH|BI|BIZ|BJ|BM|BN|BO|BR|BS|BT|BV|BW|BY|BZ|CA|CAT|CC|CD|CF|CG|CH|CI|CK|CL|CM|CN|CO|COM|COOP|CR|CU|CV|CW|CX|CY|CZ|DE|DJ|DK|DM|DO|DZ|EC|EDU|EE|EG|ER|ES|ET|EU|FI|FJ|FK|FM|FO|FR|GA|GB|GD|GE|GF|GG|GH|GI|GL|GM|GN|GOV|GP|GQ|GR|GS|GT|GU|GW|GY|HK|HM|HN|HR|HT|HU|ID|IE|IL|IM|IN|INFO|INT|IO|IQ|IR|IS|IT|JE|JM|JO|JOBS|JP|KE|KG|KH|KI|KM|KN|KP|KR|KW|KY|KZ|LA|LB|LC|LI|LK|LR|LS|LT|LU|LV|LY|MA|MC|MD|ME|MG|MH|MIL|MK|ML|MM|MN|MO|MOBI|MP|MQ|MR|MS|MT|MU|MUSEUM|MV|MW|MX|MY|MZ|NA|NAME|NC|NE|NET|NF|NG|NI|NL|NO|NP|NR|NU|NZ|OM|ORG|PA|PE|PF|PG|PH|PK|PL|PM|PN|POST|PR|PRO|PS|PT|PW|PY|QA|RE|RO|RS|RU|RW|SA|SB|SC|SD|SE|SG|SH|SI|SJ|SK|SL|SM|SN|SO|SR|ST|SU|SV|SX|SY|SZ|TC|TD|TEL|TF|TG|TH|TJ|TK|TL|TM|TN|TO|TP|TR|TRAVEL|TT|TV|TW|TZ|UA|UG|UK|US|UY|UZ|VA|VC|VE|VG|VI|VN|VU|WF|WS|XXX|YE|YT|ZA|ZM|ZW)",
		    ipaddr    = "(?:(?:[0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.){3}(?:[0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])";
		this.linkregex = new RegExp(
			"(?:^|[\\s\\(\\[])" + //Match valid delimiters for multi URL lines
			"(((?:https?|ftp):\\/\\/)?" + //Begin URL group, match and group protocol (if any).
			"(?:[\\w]+(?::[" + urlchars + "]*)?@)?" + //Match basic auth user/password prefix
			"(?:" + ipaddr + "|(?:"+ // match an ip address or domain
			"(?:[\\w-]+\\.)+" + //match subdomains and domain
			tlds + //match valid+accepted TLDs, we don't care about punycode
			"))(?:\\/[" + urlchars + "]*)?)" + //match path and query, END URL group
			"(?=$|[\\s\\)\\]])", // Look ahead for valid delimiters
			"gim"
		);
		return this;
	}
	destiny.fn.UrlFormatter.prototype.encodeUrl = function(url){
		return url.replace(/[&"'<>]/g, function(c){
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