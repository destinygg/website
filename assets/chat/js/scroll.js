/* global $ */

require('nanoscroller');

class ChatScrollPlugin {

    constructor(el, options){
        this.scroller = $(el).nanoScroller(Object.assign({
            disableResize: true,
            preventPageScrolling: true
        }, options))[0].nanoscroller;
    }

    isPinned(){
        // 30 is used to allow the scrollbar to be just offset, but still count as scrolled to bottom
        return (!this.scroller.isActive) ? true : (this.scroller.contentScrollTop >= this.scroller.maxScrollTop - 30);
    }

    updateAndPin(pin){
        this.reset();
        if(pin) this.scroller.scrollBottom(0);
    }

    reset(){
        this.scroller.reset();
    }

}

export default ChatScrollPlugin;