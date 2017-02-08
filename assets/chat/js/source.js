/* global window */

import EventEmitter from './emitter.js';
import Logger from './log.js';

const webSocket = window['WebSocket'] || window['MozWebSocket'];

class ChatSource extends EventEmitter {

    constructor(chat, server) {
        super();
        this.log    = Logger.make(this);
        this.sock   = null;
        this.server = server;
        this.chat   = chat;
    }

    connect(){
        try {
            this.sock           = new webSocket(this.server);
            this.sock.onopen    = e => this.emit('OPEN', e);
            this.sock.onclose   = e => this.emit('CLOSE', e);
            this.sock.onerror   = e => this.emit('ERR', 'socketerror');
            this.sock.onmessage = e => this.parseAndDispatch(e);
        } catch (e) {
            this.log.error(e);
            return this.emit('ERR', 'unknown');
        }
    }

    // @param event Object {data: 'EVENT "DATA"'}
    parseAndDispatch(event){
        let eventname = event.data.split(' ', 1)[0].toUpperCase(),
              payload = event.data.substring(eventname.length+1),
                 data = null;
        try {
            data = JSON.parse(payload);
        } catch(ignored){
            data = payload;
        }
        this.log.log(eventname, data);
        this.emit('DISPATCH', data); // Event is used to hook into all dispatched events
        this.emit(eventname, data);
    }

    send(eventname, data){
        const payload = (typeof data === 'string') ? data : JSON.stringify(data);
        this.sock.send(`${eventname} ${payload}`);
    }

}

export default ChatSource;