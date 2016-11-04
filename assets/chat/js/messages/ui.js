class ChatUIMessage {

    constructor(html){
        this.ui = null;
        this.message = html;
    }

    html(){
        return `<div class="ui-msg">${this.message}</div>`;
    }

}

export default ChatUIMessage;