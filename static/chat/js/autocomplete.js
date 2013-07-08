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

	$.fn.mAutoComplete = function(options){
		return this.each(function(){
			
			var settings = $.extend({
				minWordLength: 3,
				maxResults: 5
			}, options);
		
			var results 		= new Array(),
				resultIndex 	= 0,
				originalTxt 	= '',
				addonTxt		= '',
				lastWord		= '',
				inp 			= $(this), 
				autoComplete 	= new mAutoComplete(inp, options);
				
			inp.data('mAutoComplete', autoComplete);

			// If the setSelectionRange doesnt exist, dont bother trying
			if(!inp[0].setSelectionRange)
				return this;
			
			var resetSearchResults = function(){
				results 		= [];
				resultIndex 	= 0;
				originalTxt 	= '';
				addonTxt 		= '';
			}
			
			var showAutoComplete = function(i){
				var gtxt = originalTxt.substr(0, originalTxt.lastIndexOf(lastWord)) + results[i];
				addonTxt = gtxt.substring(originalTxt.length);
				//inp.val(originalTxt + addonTxt);
				inp.val(gtxt + ' ');
				inp[0].setSelectionRange(gtxt.length+1, gtxt.length+1);
				inp[0].focus();
			}
			
			var runAutoComplete = function(){
				lastWord = autoComplete.getLastWord(inp.val());
				if(lastWord.length < settings.minWordLength) 
					return;
					
				results = autoComplete.search(lastWord, settings.maxResults);
				resultIndex = results.length-1;
				originalTxt = inp.val();
				
				if(results && results.length > 0)
					showAutoComplete(results.length-1);
			};
			
			inp.on({
				keydown: function(e){

					// If the user selected text, act as a normal input.
					if(inp[0].selectionStart < inp[0].selectionEnd)
						return true;
					
					// Ignore
					if(e.keyCode == kCodes.ENTER || e.keyCode == kCodes.SHIFT || e.keyCode == kCodes.CTRL || e.keyCode == kCodes.ALT || e.keyCode == kCodes.CAPSLOCK || e.keyCode == kCodes.NUMLOCK)
						return true;
					
					if(e.keyCode == kCodes.TAB){
						inp.val(inp.val());
						// If we have only 1 result, select it
						if(results.length == 1){
							showAutoComplete(resultIndex);
							resetSearchResults();
						}else if(results.length > 1){
							resultIndex = (resultIndex == 0) ? results.length-1 : resultIndex-1;
							showAutoComplete(resultIndex);
						}else if(results.length <= 0){
							runAutoComplete();
						}
						return false;
					}
					
					resetSearchResults();
					return true;
				}
			});
		});
	};
	
	var mAutoComplete = function(input, options){
		this.input = input;
		this.shards = {};
		this.shardIds = '1234567890_ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split();
		return this.init(input, options);
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
		}
		return this;
	};
	mAutoComplete.prototype.getShardIdByTxt = function(txt){
		return txt.substr(0,1).toUpperCase();
	};
	mAutoComplete.prototype.getLastWord = function(txt){
		var si = txt.lastIndexOf(" ");
		return (si > 0) ? txt.substring(si+1) : txt;
	};
	mAutoComplete.prototype.search = function(txt, limit){
		txt = txt.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
		var res  = [], 
			f 	 = new RegExp("\\b"+txt+"", "i"),
			data = this.shards[this.getShardIdByTxt(txt)] || [];
			
		search:
		for(var weight in data){
			for(var n in data[weight]){
				if(res.length >= limit) 
					break search;
				if(f.test(data[weight][n])) 
					res.push(data[weight][n]);
			}
		}
		return res;
	};
})();