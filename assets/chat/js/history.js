/* global Math */

import ChatStore from './store.js';
import Logger from './log.js';

class ChatInputHistory {

    constructor(chat){
        this.log        = Logger.make(this);
        this.input      = $(chat.input);
        this.history    = ChatStore.read('chat.history') || [];
        this.index      = -1;
        this.lastinput  = '';
        this.maxentries = 20;
        this.input.on('keyup', e => {
            if (!(e.shiftKey || e.metaKey || e.ctrlKey) && (e.which === 38 || e.which === 40))
                this.show(e.which === 38 ? -1 : 1); // if up arrow we subtract otherwise add
            else
                this.index = -1;
        });
    }

    show(direction){
        const dir = direction === -1 ? 'UP':'DOWN';
        this.log.debug(`Show ${dir}(${direction}) index ${this.index} total ${this.history.length}`);
        // if we are not currently showing any lines from the history
        if (this.index < 0) {
            // if up arrow
            if (direction === -1) {
                // set the current line to the end if the history, do not subtract 1
                // that's done later
                this.index = this.history.length;
                // store the typed in message so that we can go back to it
                this.lastinput = this.input.val();

                if (this.index <= 0) // nothing in the history, bail out
                    return;
                // down arrow, but nothing to show
            } else
                return;
        }

        const index = this.index + direction;
        // out of bounds
        if (index >= this.history.length || index < 0) {
            // down arrow was pressed to get back to the original line, reset
            if (index >= this.history.length) {
                this.input.val(this.lastinput);
                this.index = -1;
            }
            return;
        }

        this.index = index;
        this.input.val(this.history[index]);
    }

    add(message){
        this.log.debug('Add', message);
        this.index = -1;
        // dont add entry if the last entry is the same
        if(this.history.length > 0 && this.history[this.history.length-1] === message)
            return;
        this.history.push(message);
        // limit entries
        if (this.history.length > this.maxentries)
            this.history = this.history.slice(0, this.history.length-this.maxentries);
        // set the current index to the start
        ChatStore.write('chat.history', this.history);
    }
}

export default ChatInputHistory;