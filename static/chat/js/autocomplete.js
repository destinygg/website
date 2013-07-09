(function(){
			
	var kCodes = {
		ENTER		: 13,
		RIGHT		: 39,
		END			: 35,
		TAB			: 9,
		SHIFT		: 16,
		CTRL		: 17,
		ALT			: 18,
		CAPSLOCK	: 20,
		NUMLOCK		: 144
	};
	
	var mAutoComplete = function(input, options){
		this.input = input;
		this.shards = {};
		this.shardIds = '1234567890_ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
		return this.init(input, options);
	};
	mAutoComplete.prototype.getShardIdByTxt = function(txt){
		return txt.substr(0,1).toUpperCase();
	};
	mAutoComplete.prototype.addData = function(data, weight){
		this.shardData(data, weight);
		return this;
	};
	mAutoComplete.prototype.init = function(input, options){
		for(var i=0; i<this.shardIds.length; ++i){
			this.shards[this.shardIds[i]] = {};
		}
		return this;
	};
	mAutoComplete.prototype.shardData = function(data, weight){
		for(var n in data){
			var id = this.getShardIdByTxt(data[n]);
			if(typeof this.shards[id] != 'object')
				this.shards[id] = {};
			if(typeof this.shards[id][weight] != 'object')
				this.shards[id][weight] = [];
			this.shards[id][weight].push(data[n]);
			this.shards[id][weight].sort(function(x, y){return x > y});
		}
		return this;
	};
	mAutoComplete.prototype.getLastWord = function(txt){
		var si = txt.lastIndexOf(" ");
		var s = (si > 0) ? txt.substring(si+1) : txt;
		return s.trim();
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
	mAutoComplete.prototype.search = function(txt, limit){
		txt = txt.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
		var res  = [], 
			f    = new RegExp("\\b"+txt+"", "i"),
			data = this.shards[this.getShardIdByTxt(txt)] || [];
		search:
		for(var weight in data){
			for(var n in data[weight]){
				if(res.length >= limit) {
					break search;
				}
				if(f.test(data[weight][n])) {
					res.push(data[weight][n]);
				}
			}
		}
		return res;
	};

	$.fn.mAutoComplete = function(options){
		return this.each(function(){
			
			var settings = $.extend({
				minWordLength: 3,
				maxResults: 5
			}, options);
		
			var results 		= new Array(),
				resultIndex 	= 0,
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
				resultIndex = (resultIndex == results.length) ? 0 : resultIndex+1;
				var replace = results[resultIndex];
				if(replace){
					var pre = originalTxt.substr(0,searchWord.startIndex),
						post = originalTxt.substr(searchWord.startIndex+searchWord.word.length);
					
					if(post.substring(0,1) != " " || post.length == 0)
						post =  " " + post;
				
					// Only change the input value / move the cursor if the search word is different
					if(replace.toLowerCase() != searchWord.word.toLowerCase()){
						inp.val(pre+replace+post);
						inp[0].setSelectionRange(pre.length+replace.length+1, pre.length+replace.length+1);
						inp[0].focus();
					}
				}
			};
			
			inp.on({
				mousedown: function(e){
					resetSearchResults();
					return true;
				},
				keydown: function(e){
					// Ignore if one of these keys are pressed OR
					if(e.keyCode == kCodes.ENTER || e.keyCode == kCodes.SHIFT || e.keyCode == kCodes.CTRL || e.keyCode == kCodes.ALT || e.keyCode == kCodes.CAPSLOCK || e.keyCode == kCodes.NUMLOCK)
						return true;

					// if(inp[0].selectionStart < inp[0].selectionEnd) Care about selection?
						
					if(e.keyCode == kCodes.TAB){
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