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

$.fn.sortElements = (function(){
	var sort = [].sort;
	return function(comparator, getSortable) {
		getSortable = getSortable || function(){return this;};
		var placements = this.map(function(){
			var sortElement = getSortable.call(this), parentNode = sortElement.parentNode;
				// Since the element itself will change position, we have
				// to have some way of storing its original position in
				// the DOM. The easiest way is to have a 'flag' node:
			var nextSibling = parentNode.insertBefore(document.createTextNode(''), sortElement.nextSibling);
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

function getRandomInt (min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

String.prototype.trim = function(){
	return $.trim(this);
};

if (!String.prototype.format) {
	String.prototype.format = function() {
		var args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number) {
			return typeof args[number] != 'undefined' ? args[number] : match;
		});
	};
}