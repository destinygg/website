class ChatUIMessage {

    constructor(str){
        this.ui      = null;
        this.chat    = null;
        this.message = str;
        this.type    = 'ui';
    }

    resolve(){
        return this;
    }

    attach(chat, resolvable){
        this.chat = chat;
        this.ui = $(this.html());
        return this.ui;
    }

    wrap(content, classes=[], attr={}){
        classes.unshift(`msg-${this.type}`);
        attr['class'] = classes.join(' ');
        return $('<div>', attr).html(content)[0].outerHTML;
    }

    html(){
        return this.wrap(this.message);
    }

}

export default ChatUIMessage;