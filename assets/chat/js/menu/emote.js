/* global $ */

import ChatMenu from './menu.js';

class ChatEmoteMenu extends ChatMenu {

    constructor(ui, chat) {
        super(ui, chat);
        this.input = $(this.chat.input);
        this.temotes = $('#twitch-emotes');
        this.demotes = $('#destiny-emotes');
        this.demotes.append([...this.chat.emoticons].map(emote => ChatEmoteMenu.buildEmote(emote)).join(''));
        this.temotes.append([...this.chat.twitchemotes].map(emote => ChatEmoteMenu.buildEmote(emote)).join(''));
        this.ui.on('click', '.chat-emote', e => this.selectEmote(e.target.innerText));
    }

    selectEmote(emote){
        let value = this.input.val().toString().trim();
        this.input.val(value + (value === '' ? '':' ') +  emote + ' ').focus();
    }

    static buildEmote(emote){
        return `<div class="emote"><span title="${emote}" class="chat-emote chat-emote-${emote}">${emote}</span></div>`
    }

}

export default ChatEmoteMenu;