(function($){

    // Chat message formatters
    // must have two methods, a constructor and a .format(message[, user]) method

    // Green Text formatter
    destiny.fn.GreenTextFormatter = function(chat){
        return this;
    };

    destiny.fn.GreenTextFormatter.prototype.format = function(str, user){
        if(user && str.indexOf("&gt;") === 0){
            if(user.hasAnyFeatures(
                destiny.UserFeatures.SUBSCRIBERT3,
                destiny.UserFeatures.SUBSCRIBERT4,
                destiny.UserFeatures.SUBSCRIBERT2,
                destiny.UserFeatures.ADMIN,
                destiny.UserFeatures.MODERATOR
            ))
                str = '<span class="greentext">'+str+'</span>';
        }
        return str;
    };


    // Emote line formatter
    destiny.fn.EmoteFormatter = function(chat){
        this.emoteregex = new RegExp('(^|\\s)('+chat.emoticons.join('|')+')(?=$|\\s)');
        this.gemoteregex = new RegExp('(^|\\s)('+chat.emoticons.join('|')+')(?=$|\\s)', 'gm');
        this.twitchemoteregex = new RegExp('(^|\\s)('+chat.emoticons.join('|')+'|'+chat.twitchemotes.join('|')+')(?=$|\\s)', 'gm');
        return this;
    };

    destiny.fn.EmoteFormatter.prototype.format = function(str, user){
        var emoteregex = this.emoteregex;
        if (user && user.features.length > 0) {
            if (user.hasFeature(destiny.UserFeatures.SUBSCRIBERT0))
                emoteregex = this.twitchemoteregex;
            else
                emoteregex = this.gemoteregex;
        }
        return str.replace(emoteregex, '$1<div title="$2" class="chat-emote chat-emote-$2">$2 </div>');
    };

    // User mention formatter
    destiny.fn.MentionedUserFormatter = function(chat) {
        this.users = chat.engine.users;
        this.userregex = /((?:^|\s)@?)([a-zA-Z0-9_]{3,20})(?=$|\s|[\.\?!,])/g;
        return this;
    };

    destiny.fn.MentionedUserFormatter.prototype.format = function(str, user) {
        var users = this.users;
        return str.replace(this.userregex, function(match, prefix, nick) {
            if (users.propertyIsEnumerable(nick)) {
                return prefix + '<span class="chat-user">' + nick + '</span>';
            } else {
                return match;
            }
        });
    };

})(jQuery);
