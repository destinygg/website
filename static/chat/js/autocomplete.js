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
	mAutoComplete.prototype.addData = function(data, weight){
		this.shardData(data, weight);
		return this;
	};
	mAutoComplete.prototype.init = function(input, options){
		return this;
	};
	mAutoComplete.prototype.shardData = function(data, weight){
		var sortShards = [];
		for(var n in data){
			var id = this.getShardIdByTxt(data[n]);
			if(typeof this.shards[id] != 'object')
				this.shards[id] = {};
			if(typeof this.shards[id][weight] != 'object')
				this.shards[id][weight] = [];
			
			if($.inArray(data[n], this.shards[id][weight]) < 0){
				this.shards[id][weight].push(data[n]);
				sortShards.push([id,weight]);
			}
		}
		for(var x in sortShards)
			this.shards[sortShards[x][0]][sortShards[x][1]].sort(function(x, y){return x > y});
		
		sortShards = null;
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
				var replace = results[resultIndex];
				if(replace){
					var pre = originalTxt.substr(0,searchWord.startIndex),
						post = originalTxt.substr(searchWord.startIndex+searchWord.word.length);
					
					if(post.substring(0,1) != " " || post.length == 0)
						post =  " " + post;
				
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