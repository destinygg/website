import ChatScrollPlugin from './scroll';
const tagcolors = [
    "green",
    "yellow",
    "orange",
    "purple",
    "blue",
    "sky",
    "lime",
    "pink"
];

class ChatWindow {

    constructor(name, type='', label=''){
        this.name = name;
        this.label = label;
        this.maxlines = 0;
        this.linecount = 0;
        this.locks = 0;
        this.waspinned = true;
        this.scrollplugin = null;
        this.visible = false;
        this.tag = null;
        this.ui = $(`<div id="chat-win-${name}" class="chat-output ${type} nano" style="display: none;">\
                        <div class="chat-lines nano-content"></div>\
                        <div class="chat-scroll-notify">More messages below</div>\
                     </div>`);
        this.lines = this.ui.find('.chat-lines');
    }

    destroy(){
        this.ui.remove();
        this.scrollplugin.destroy();
        return this;
    }

    into(chat){
        this.maxlines = chat.settings.get('maxlines');
        this.scrollplugin = new ChatScrollPlugin(chat, this.ui);
        this.tag = chat.taggednicks.get(this.name) || tagcolors[Math.floor(Math.random()*tagcolors.length)];
        chat.output.append(this.ui);
        chat.addWindow(this.name.toLowerCase(), this);
        chat.windowToFront(this.name.toLowerCase());
        return this;
    }

    show(){
        if(!this.visible){
            this.visible = true;
            this.ui.show();
        }
    }

    hide(){
        if(this.visible) {
            this.visible = false;
            this.ui.hide();
        }
    }

    addline(e){
        this.lines.append(e);
        this.linecount++;
    }

    getlines(sel){
        return this.lines.children(sel);
    }

    removelines(sel){
        const remove = this.lines.children(sel);
        this.linecount -= remove.length;
        remove.remove();
    }

    locked(){
        return this.locks > 0;
    }

    lock(){
        this.locks++;
        if(this.locks === 1) {
            this.waspinned = this.scrollplugin.isPinned();
        }
    }

    unlock(){
        this.locks--;
        if(this.locks === 0) {
            this.scrollplugin.updateAndPin(this.waspinned);
        }
    }

    // Rid excess chat lines if the chat is pinned
    // Get the scroll position before adding the new line / removing old lines
    cleanup(){
        if(this.scrollplugin.isPinned() || this.waspinned) {
            const lines = this.lines.children();
            if(lines.length >= this.maxlines){
                const remove = lines.slice(0, lines.length - this.maxlines);
                this.linecount -= remove.length;
                remove.remove();
            }
        }
    }

    updateAndPin(pin=true){
        this.scrollplugin.updateAndPin(pin);
    }

}

export default ChatWindow;