(function(){
	mAutoComplete = function(input, options) {
		this.input = $(input);

		if (!input[0].setSelectionRange)
			return this;

		this.minWordLength = 2;
		this.maxResults    = 10;
		this.buckets       = {};
		this.expireUsers   = $.proxy(this.expireUsers, this);
		this.resetSearch();

		setInterval(this.expireUsers, 300000); // 5 minutes

		var self = this;
		this.input.on({
			mousedown: function(e) {
				self.resetSearch();
				return true;
			},
			keydown: function(e) {
				if (e.keyCode == 9) { // if TAB
					if (self.searchResults.length <= 0) {
						self.resetSearch();
						self.checkCurrentWord();
					}
					self.showAutoComplete();
					return false;
				}

				// Cancel the search and continue the keydown
				self.resetSearch();
				return true;
			}
		});

		return this;
	};
	mAutoComplete.prototype.getBucketId = function(str) {
		if (str.length == 0)
			return "";

		return str[0].toLowerCase();
	};
	mAutoComplete.prototype.getBucket = function(nick, weight, isemote, ispromoted) {
		var id = this.getBucketId(nick);

		if(!this.buckets[id])
			this.buckets[id] = {};

		if (!this.buckets[id][nick])
			this.buckets[id][nick] = {
				data: nick,
				weight: weight,
				isemote: !!isemote,
				ispromoted: !!ispromoted
			};

		return this.buckets[id][nick];
	};
	mAutoComplete.prototype.addEmote = function(emote){
		this.getBucket(emote, 1, true, false);

		return this;
	};
	mAutoComplete.prototype.addNick = function(nick) {
		this.getBucket(nick, 1, false, false);

		return this;
	};
	mAutoComplete.prototype.updateNick = function(nick) {
		var weight = Date.now();
		var data = this.getBucket(nick, weight, false, false);

		data.weight = weight;
		return this;
	};
	mAutoComplete.prototype.promoteNick = function(nick) {
		var weight = Date.now();
		var data = this.getBucket(nick, weight, false, false);

		if (data.isemote)
			return this;

		data.weight = weight;
		data.ispromoted = true;
		return this;
	};
	mAutoComplete.prototype.getCaretWord = function() {
		var value      = this.input.val(),
		    bareinput  = this.input[0],
		    pre        = value.substring(0, bareinput.selectionStart),
		    post       = value.substring(bareinput.selectionStart),
		    startCaret = pre.lastIndexOf(" ") + 1,
		    endCaret   = post.indexOf(" ");

		if (startCaret > 0)
			pre = pre.substring(startCaret);

		if (endCaret > -1)
			post = post.substring(0, endCaret);

		return {
			pre       : pre,
			post      : post,
			word      : pre + post,
			startIndex: startCaret
		};
	};
	mAutoComplete.prototype.cmp = function(a, b) {
		// order promoted things first
		if (a.ispromoted != b.ispromoted)
			return a.ispromoted && !b.ispromoted? -1: 1;

		// order emotes second
		if (a.isemote != b.isemote)
			return a.isemote && !b.isemote? -1: 1;

		// order according to recency third
		if (a.weight != b.weight)
			return a.weight > b.weight? -1: 1;

		// order lexically fourth
		var a = a.data.toLowerCase(),
		    b = b.data.toLowerCase();

		if (a == b)
			return 0;

		return a > b? 1: -1;
	};
	mAutoComplete.prototype.search = function(str, limit) {
		// escape the text being inserted into the regexp
		str = str.trim().replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
		var res  = [],
		    f    = new RegExp("^"+str, "i"),
		    data = this.buckets[this.getBucketId(str)] || {};
		
		for (var nick in data) {
			if (!data.hasOwnProperty(nick))
				continue;

			if (f.test(nick))
				res.push(data[nick]);
		}

		res.sort(this.cmp);
		return res.slice(0, limit);
	};
	mAutoComplete.prototype.expireUsers = function() {
		// if the user hasnt spoken in the last 5 minutes, reset the weight
		// so that emotes can be ordered before the user again
		var fiveminutesago = Date.now() - 300000;
		for (var i in this.buckets) {
			if (!this.buckets.hasOwnProperty(i))
				continue;

			for(var j in this.buckets[i]) {
				if (!this.buckets[i].hasOwnProperty(j))
					continue;

				var data = this.buckets[i][j];
				if (data.isemote || data.weight > fiveminutesago)
					continue;

				data.weight = 1;
				data.ispromoted = false;
			};
		};
	};
	mAutoComplete.prototype.markLastComplete = function() {
		if(!this.lastComplete)
			return

		var data = this.buckets[this.getBucketId(this.lastComplete)] || {};
		if (!data[this.lastComplete] || data[this.lastComplete].isemote)
			return this.lastComplete = null;

		this.promoteNick(this.lastComplete);
		this.lastComplete = null;
	};
	mAutoComplete.prototype.resetSearch = function() {
		this.origVal       = null;
		this.searchResults = [];
		this.searchIndex   = -1;
		this.searchWord    = null;
	};
	mAutoComplete.prototype.checkCurrentWord = function() {
		this.searchWord = this.getCaretWord();
		if (this.searchWord.word.length < this.minWordLength)
			return;

		this.searchResults = this.search(this.searchWord.word, this.maxResults);
		this.origVal = this.input.val();
	};
	mAutoComplete.prototype.showAutoComplete = function() {
		if (this.searchIndex >= this.searchResults.length - 1)
			this.searchIndex = 0;
		else
			this.searchIndex = this.searchIndex + 1;

		var result = this.searchResults[this.searchIndex];
		if (!result)
			return;

		this.lastComplete = result.data;
		var pre  = this.origVal.substr(0, this.searchWord.startIndex),
		    post = this.origVal.substr(this.searchWord.startIndex + this.searchWord.word.length);

		// always add a space after our completion if there isn't one since people
		// would add one anyway
		if (post[0] != " " || post.length == 0)
			post = " " + post;

		if (result.data == this.searchWord.word)
			return;

		var replace = result.data;
		this.input.focus();
		this.input.val(pre + replace + post);

		// If the cursor is at the end of the string, refocus input to shift the inputs overflow / focus
		if (post.trim() == "")
			this.input.blur().focus();
		else // If the caret is in the middle of other text, just move the cursor
			this.input[0].setSelectionRange(pre.length + replace.length, pre.length + replace.length);

	};

})();
