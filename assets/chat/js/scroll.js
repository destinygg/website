/* global $ */

require('nanoscroller');

class ChatScrollPlugin {

    constructor(chat, e, options={sliderMinHeight: 40, tabIndex: 1}){
        const el = $(e);
        if(el.find('.chat-scroll-notify').length > 0) {
            el.on('update', () => el.toggleClass('chat-unpinned', !this.isPinned())); //debounce
            el.on('mousedown', '.chat-scroll-notify', () => false);
            el.on('mouseup', '.chat-scroll-notify', () => {
                this.updateAndPin(true);
                return false;
            });
        }
        this.scroller = el.nanoScroller(Object.assign({
            disableResize: true,
            preventPageScrolling: true
        }, options))[0].nanoscroller;
    }

    isPinned(){
        // 30 is used to allow the scrollbar to be just offset, but still count as scrolled to bottom
        return !this.scroller.isActive ? true : (this.scroller.contentScrollTop >= this.scroller.maxScrollTop - 30);
    }

    updateAndPin(pin){
        this.reset();
        if(pin) this.scroller.scrollBottom(0);
    }

    reset(){
        this.scroller.reset();
    }

    destroy(){
        this.scroller.destroy();
    }

}

export default ChatScrollPlugin;