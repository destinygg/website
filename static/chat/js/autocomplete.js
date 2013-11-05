(function(){
			
	var kCodes = {
		TAB : 9
	};
	
	var mAutoComplete = function(input, options){
		this.input = input;
		this.shards = {};
		return this.init(input, options);
	};
	mAutoComplete.prototype.getShardIdByTxt = function(txt){
		return txt.substr(0,1).toUpperCase();
	};
	mAutoComplete.prototype.addData = function(nick, weight){
		var id        = this.getShardIdByTxt(nick);
		
		if(!this.shards[id])
			this.shards[id] = {};
		
		if (this.shards[id][nick])
			this.shards[id][nick].weight = weight;
		else
			this.shards[id][nick] = {nick: nick, weight: weight};
		
		return this;
	};
	mAutoComplete.prototype.init = function(input, options){
		this.expireUsers = $.proxy(this.expireUsers, this);
		setInterval(this.expireUsers, 300000); // five minutes
		return this;
	};
	mAutoComplete.prototype.getCaretWord = function(inp){
		var pre = inp.val().substring(0, inp[0].selectionStart),
			post = inp.val().substring(inp[0].selectionStart),
			startCaret = pre.lastIndexOf(" ")+1, 
			endCaret = post.indexOf(" ");
		if(startCaret > 0)
			pre = pre.substring(startCaret);
		if(endCaret > -1)
			post = post.substring(0, endCaret);
		return {pre: pre, post: post, word: pre+post, startIndex: startCaret};
	};
	mAutoComplete.prototype.cmp = function(a, b) {
		if (a.weight == b.weight) {
			// if the weight is the same, order lexically
			var a = a.nick.toLowerCase(),
			    b = b.nick.toLowerCase();
			
			if (a == b)
				return 0;
			
			return a > b? 1: -1;
		}
		return a.weight > b.weight? -1: 1;
	};
	mAutoComplete.prototype.search = function(txt, limit){
		// escape the text being inserted into the regexp
		txt = txt.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
		var res  = [],
		    f    = new RegExp("^"+txt, "i"),
		    data = this.shards[this.getShardIdByTxt(txt)] || {};
		
		$.each(data, function(nick, v) {
			if (f.test(nick))
				res.push(v);
		});
		
		res.sort(this.cmp);
		return res;
	};
	mAutoComplete.prototype.expireUsers = function() {
		// if the user hasnt spoken in the last five minutes, reset the weight
		// so that emotes can be ordered before the user again
		var fiveminutesago = (new Date).getTime() - 300000;
		for (var i in this.shards) {
			if (!this.shards.hasOwnProperty(i))
				continue;

			for(var j in this.shards[i]) {
				if (!this.shards[i].hasOwnProperty(j))
					continue;

				// dont touch emotes or already reset users
				var nick = this.shards[i][j]
				if (nick.weight > 2 && nick.weight < fiveminutesago)
					nick.weight = 1;
			};
		};
	};

	$.fn.mAutoComplete = function(options){
		return this.each(function(){
			
			var settings = $.extend({
				minWordLength: 3,
				maxResults: 5,
				triggerKeys: [kCodes.TAB]
			}, options);
		
			var results 		= new Array(),
				resultIndex 	= -1,
				searchWord		= '',
				originalTxt 	= '',
				inp 			= $(this), 
				autoComplete 	= new mAutoComplete(inp, options);
				
			inp.data('mAutoComplete', autoComplete);

			if(!inp[0].setSelectionRange)
				return this;
			
			var resetSearchResults = function(){
				results = [];
				resultIndex = -1;
				searchWord = '';
				originalTxt = '';
			};
			
			var checkCurrentWord = function(){
				searchWord = autoComplete.getCaretWord(inp);
				if(searchWord.word.length < settings.minWordLength) 
					return;
				results = autoComplete.search(searchWord.word, settings.maxResults);
				originalTxt = inp.val();
			};
			
			var showAutoComplete = function(){
				resultIndex = (resultIndex >= results.length-1) ? 0 : resultIndex+1;
				var replace = (results[resultIndex] || {}).nick;
				if(replace){
					var pre  = originalTxt.substr(0,searchWord.startIndex),
					    post = originalTxt.substr(searchWord.startIndex+searchWord.word.length);
					
					if(post.substring(0,1) != " " || post.length == 0)
						post = " " + post;
				
					// Only change the input value / move the cursor if the search word is different
					if(replace.toLowerCase() != searchWord.word.toLowerCase()){
						inp.focus();
						if(post.substring(0,1) != " " || post.length == 0)
							replace =  replace + " ";
						inp.val(pre+replace+post);
						if(post.trim() == ''){
							// If the cursor is at the end of the string, refocus input to shift the inputs overflow / focus
							inp.blur().focus();
						}else{
							// If the caret is in the middle of other text, just move the cursor
							inp[0].setSelectionRange(pre.length+replace.length, pre.length+replace.length);
						}
					}
				}
			};
			
			inp.on({
				mousedown: function(e){
					resetSearchResults();
					return true;
				},
				keydown: function(e){

					if($.inArray(e.keyCode, settings.triggerKeys) >= 0){
						if(results.length <= 0){
							resetSearchResults();
							checkCurrentWord();
						}
						showAutoComplete();
						// Always cancel tab? the user is used to not loosing focus
						return false;
					}

					// Cancel the search and continue the keydown
					resetSearchResults();
					return true;
				}
			});
		});
	};
})();