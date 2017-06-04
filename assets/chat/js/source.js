/* global window */

import EventEmitter from './emitter.js';
const webSocket = window['WebSocket'] || window['MozWebSocket'];

class ChatSource extends EventEmitter {

    constructor() {
        super();
        this.sock = null;
    }

    connect(url){
        try {
            if(this.sock !== null) {
                this.disconnect();
                this.sock.onopen = null;
                this.sock.onclose = null;
                this.sock.onerror = null;
                this.sock.onmessage = null;
                this.sock = null;
            }
            this.emit('CONNECTING');
            this.sock = new webSocket(url);
            this.sock.onopen = e => this.emit('OPEN', e);
            this.sock.onclose = e => this.emit('CLOSE', e);
            this.sock.onerror = e => this.emit('ERR', 'socketerror');
            this.sock.onmessage = e => this.parseAndDispatch(e);
        } catch (e) {
            console.error(e);
            return this.emit('ERR', 'unknown');
        }
    }

    connected(){
        return this.sock && this.sock.readyState === this.sock.OPEN;
    }

    disconnect(){
        if(this.sock && this.sock.readyState !== this.sock.CLOSED) this.sock.close();
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
        this.emit('DISPATCH', {data: data, event: eventname}); // Event is used to hook into all dispatched events
        this.emit(eventname, data);
    }

    send(eventname, data){
        const payload = (typeof data === 'string') ? data : JSON.stringify(data);
        if(this.connected()){
            this.sock.send(`${eventname} ${payload}`);
        } else {
            this.emit('ERR', 'notconnected');
        }
    }

}

export default ChatSource;