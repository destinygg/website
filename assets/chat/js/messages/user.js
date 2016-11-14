/* global $, destiny */

import ChatMessage from './message.js';
import UserFeatures from '../features.js';

class ChatUserMessage extends ChatMessage {

    constructor(message, user, timestamp) {
        super(message, timestamp, 'user');
        this.user = user;
        this.highlighted = false;
        this.prepareMessage();
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

    wrapUser(user){
        const features = (user.features.length > 0) ? `<span class="features">${ChatUserMessage.getFeatureHTML(user)}</span>` : '';
        return `${features} <a class="user ${user.features.join(' ')}">${user.username}</a>`;
    }

    html(){
        const classes = [], attr = {};
        if (this.user && this.user.username)
            attr['data-username'] = this.user.username.toLowerCase();
        if(this.chat.user && this.chat.user.username == this.user.username)
            classes.push('msg-own');
        if(this.isSlashMe)
            classes.push('msg-emote');
        if(this.highlighted)
            classes.push('msg-highlight');
        if(this.chat.lastMessage && this.chat.lastMessage.user && this.user && this.chat.lastMessage.user.username == this.user.username)
            classes.push('msg-continue');
        return this.wrap(this.wrapTime() + ' ' + this.wrapUser(this.user) + ' ' + this.wrapMessage(), classes, attr);
    }

    static getFeatureHTML(user){
        let icons = '';

        if(user.hasFeature(UserFeatures.SUBSCRIBERT4))
            icons += '<i class="icon-subscribert4" title="Subscriber (T4)"/>';
        else if(user.hasFeature(UserFeatures.SUBSCRIBERT3))
            icons += '<i class="icon-subscribert3" title="Subscriber (T3)"/>';
        else if(user.hasFeature(UserFeatures.SUBSCRIBERT2))
            icons += '<i class="icon-subscribert2" title="Subscriber (T2)"/>';
        else if(user.hasFeature(UserFeatures.SUBSCRIBERT1))
            icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';
        else if(!user.hasFeature(UserFeatures.SUBSCRIBERT0) && user.hasFeature(UserFeatures.SUBSCRIBER))
            icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';

        for(const feature of user.features){
            switch(feature){
                case UserFeatures.SUBSCRIBERT0 :
                    icons += '<i class="icon-minitwitch" title="Twitch subscriber"/>';
                    break;
                case UserFeatures.BOT :
                    icons += '<i class="icon-bot" title="Bot"/>';
                    break;
                case UserFeatures.BOT2 :
                    icons += '<i class="icon-bot2" title="Bot"/>';
                    break;
                case UserFeatures.NOTABLE :
                    icons += '<i class="icon-notable" title="Notable"/>';
                    break;
                case UserFeatures.TRUSTED :
                    icons += '<i class="icon-trusted" title="Trusted"/>';
                    break;
                case UserFeatures.CONTRIBUTOR :
                    icons += '<i class="icon-contributor" title="Contributor"/>';
                    break;
                case UserFeatures.COMPCHALLENGE :
                    icons += '<i class="icon-compchallenge" title="Composition Winner"/>';
                    break;
                case UserFeatures.EVE :
                    icons += '<i class="icon-eve" title="Eve"/>';
                    break;
                case UserFeatures.SC2 :
                    icons += '<i class="icon-sc2" title="Starcraft 2"/>';
                    break;
                case UserFeatures.BROADCASTER :
                    icons += '<i class="icon-broadcaster" title="Broadcaster"/>';
                    break;
            }
        }
        return icons;
    }

}

export default ChatUserMessage;
