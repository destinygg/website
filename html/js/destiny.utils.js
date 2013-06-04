// Finds text urls and wraps them in a anchor
String.prototype.linkify = function(){
	return this.replace(/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;'">\:\s\<\>\)\]\!])/g, function(url) {
		return '<a href="'+url+'">'+url+'</a>';
	});
};
// finds text with @[twitterId] and replaces it with a twitter reply link
String.prototype.twitterReply = function(){
	return this.replace(/\B@([_a-z0-9]+)/ig, function(reply) {
		return reply.charAt(0)+'<a href="http://twitter.com/'+reply.substring(1)+'">'+reply.substring(1)+'</a>';
	});
};

jQuery.fn.replaceClass = function(toReplace,replaceWith){
	return this.each(function(){
		return $(this).removeClass(toReplace).addClass(replaceWith);
	});
}

// Link and add @ links to twitter text
String.prototype.twitterText = function(){
	return this.linkify().twitterReply();
};

function htmlEncode(value){ return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); };

$.fn.loadImages = function(options){
	options = $.extend({}, {attr:'src'}, options);
	return this.find('img[data-'+options.attr+']').each(function(){
		var img = $(this), url = img.data(options.attr);
		if(url != '' && url != null){
			var nimg = img.clone();
			nimg.one('load', function(){img.replaceWith(nimg);});
			nimg.removeAttr(options.attr).removeAttr('data-'+options.attr).attr('src', url);
		};
	});
};

function getChampIcon(name){
	return destiny.cdn + '/img/lol/champions/'+name.replace(' ', '-').replace('\'','').replace('.','').toLowerCase()+'.png';
};

$.fn.sortElements = (function(){
	var sort = [].sort;
	return function(comparator, getSortable) {
		getSortable = getSortable || function(){return this;};
		var placements = this.map(function(){
			var sortElement = getSortable.call(this),
				parentNode = sortElement.parentNode,
				// Since the element itself will change position, we have
				// to have some way of storing its original position in
				// the DOM. The easiest way is to have a 'flag' node:
				nextSibling = parentNode.insertBefore(document.createTextNode(''), sortElement.nextSibling);
			return function() {
				if (parentNode === this) {
					throw new Error("You can't sort elements if any one is a descendant of another.");
				}
				// Insert before flag:
				parentNode.insertBefore(this, nextSibling);
				// Remove flag:
				parentNode.removeChild(nextSibling);
			};
		});

		return sort.call(this, comparator).each(function(i){
			placements[i].call(getSortable.call(this));
		});
	};
})();