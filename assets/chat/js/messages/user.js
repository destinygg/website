/* global $, destiny */

import ChatMessage from './message.js';

class ChatUserMessage extends ChatMessage {

    constructor(message, user, timestamp) {
        super(message, timestamp, 'user');
        this.user = user;
    }

    prepareMessage(){
        this.isSlashMe = false;
        if (this.message.substring(0, 4) === '/me ') {
            this.isSlashMe = true;
            this.message = this.message.substring(4);
        } else if (this.message.substring(0, 2) === '//') {
            this.message = this.message.substring(1);
        }
    }

    wrap(html, css){
        if (this.user && this.user.username){
            return '<div class="'+this.type+'-msg'+((css) ? ' '+css:'')+'" data-username="'+this.user.username.toLowerCase()+'">'+html+'</div>';
        } else {
            return '<div class="'+this.type+'-msg'+((css) ? ' '+css:'')+'">'+html+'</div>';
        }
    }

    wrapUser(user){
        return ((this.isSlashMe) ? '': ChatUserMessage.getFeatureHTML(user)) +' <a class="user '+ user.features.join(' ') +'">' +user.username+'</a>';
    }

    html(){
        let ui = null;
        const lastMessage = destiny.chat.gui.lastMessage;
        if(lastMessage && lastMessage.user && this.user && lastMessage.user.username == this.user.username){
            if(this.isSlashMe)
                ui = this.wrap(this.wrapTime() + ' *' + this.wrapUser(this.user) + ' ' + this.wrapMessage());
            else
                ui = this.wrap(this.wrapTime() + ' <span class="continue">&gt;</span> ' + this.wrapMessage(), 'continue');
        } else {
            ui = this.wrap(this.wrapTime() + ' ' + ((!this.isSlashMe) ? '' : '*') + this.wrapUser(this.user) + ((!this.isSlashMe) ? ': ' : ' ') + this.wrapMessage());
        }
        $(ui).toggleClass('own-msg', this.user && destiny.chat.user && destiny.chat.user.username == this.user.username);
        $(ui).toggleClass('emote', this.isSlashMe);
        return ui;
    }

    static getFeatureHTML(user){
        let icons = '';
        if(user.hasFeature(destiny.UserFeatures.SUBSCRIBERT4))
            icons += '<i class="icon-subscribert4" title="Subscriber (T4)"/>';
        else if(user.hasFeature(destiny.UserFeatures.SUBSCRIBERT3))
            icons += '<i class="icon-subscribert3" title="Subscriber (T3)"/>';
        else if(user.hasFeature(destiny.UserFeatures.SUBSCRIBERT2))
            icons += '<i class="icon-subscribert2" title="Subscriber (T2)"/>';
        else if(user.hasFeature(destiny.UserFeatures.SUBSCRIBERT1))
            icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';
        else if(!user.hasFeature(destiny.UserFeatures.SUBSCRIBERT0) && user.hasFeature(destiny.UserFeatures.SUBSCRIBER))
            icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';

        for (var i = 0; i < user.features.length; i++) {
            switch(user.features[i]){
                case destiny.UserFeatures.SUBSCRIBERT0 :
                    icons += '<i class="icon-minitwitch" title="Twitch subscriber"/>';
                    break;
                case destiny.UserFeatures.BOT :
                    icons += '<i class="icon-bot" title="Bot"/>';
                    break;
                case destiny.UserFeatures.BOT2 :
                    icons += '<i class="icon-bot2" title="Bot"/>';
                    break;
                case destiny.UserFeatures.NOTABLE :
                    icons += '<i class="icon-notable" title="Notable"/>';
                    break;
                case destiny.UserFeatures.TRUSTED :
                    icons += '<i class="icon-trusted" title="Trusted"/>';
                    break;
                case destiny.UserFeatures.CONTRIBUTOR :
                    icons += '<i class="icon-contributor" title="Contributor"/>';
                    break;
                case destiny.UserFeatures.COMPCHALLENGE :
                    icons += '<i class="icon-compchallenge" title="Composition Winner"/>';
                    break;
                case destiny.UserFeatures.EVE :
                    icons += '<i class="icon-eve" title="Eve"/>';
                    break;
                case destiny.UserFeatures.SC2 :
                    icons += '<i class="icon-sc2" title="Starcraft 2"/>';
                    break;
                case destiny.UserFeatures.BROADCASTER :
                    icons += '<i class="icon-broadcaster" title="Broadcaster"/>';
                    break;
            }
        }
        return icons;
    }

}

export default ChatUserMessage;
