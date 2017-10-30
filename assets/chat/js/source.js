/* global window */

import EventEmitter from './emitter'
const WebSocket = window['WebSocket'] || window['MozWebSocket']

/**
 * Handles the websocket connection, opening, closing, retrying
 * and parsing the standard format from the golang dgg service `$EVENT ${DATA}`
 * extends the EventEmitter so you can bind to the events using source.on(name, fn)
 *
 * e.g.
 * let s = new ChatSource()
 *
 * s.on('OPEN', ... )           Connection is established
 * s.on('CLOSE', ... )          Connection is closed
 * s.on('CONNECTING', ... )     A new connection is created, before connect is called
 * s.on('SOCKETERROR', ... )    When a socket level error occurs
 * s.on('ERR', ... )            When a chat error occurs `ERR 'code'`
 * s.on('DISPATCH', ... )       Any socket.onmessage event
 * s.on('$EVENT', ... )         Custom event sent from the chat server e.g. `NAMES { ... }`
 *
 * s.connect('wss://localhost')
 */
class ChatSource extends EventEmitter {

    constructor() {
        super()
        this.socket = null
        this.url = null
        this.retryOnDisconnect = true
        this.retryAttempts = 0
        this.retryTimer = null
    }

    isConnected(){
        return this.socket && this.socket.readyState === this.socket.OPEN
    }

    connect(url){
        this.url = url
        this.retryAttempts++
        try {
            if(this.retryTimer !== null){
                clearTimeout(this.retryTimer)
                this.retryTimer = null
            }
            if(this.socket !== null) {
                this.socket.onopen = null
                this.socket.onclose = null
                this.socket.onerror = null
                this.socket.onmessage = null
                this.disconnect()
                // we null the socket, without waiting for the disconnect
                // possible orphaned connections
                this.socket = null
            }
            this.emit('CONNECTING', this.url)
            this.socket = new WebSocket(this.url)
            this.socket.onopen = e => this.onOpen(e)
            this.socket.onclose = e => this.onClose(e)
            this.socket.onmessage = e => this.onMsg(e)
            this.socket.onerror = e => this.emit('SOCKETERROR', e)
        } catch (e) {
            this.emit('SOCKETERROR', e)
        }
    }

    disconnect(){
        if(this.socket && this.socket.readyState !== this.socket.CLOSED) this.socket.close()
    }

    onOpen(e){
        this.emit('OPEN', e)
        this.retryAttempts = 0
        this.retryOnDisconnect = true
    }

    onClose(e){
        let retryMilli = 0;
        if(this.retryOnDisconnect) {
            // If a disconnect is experienced after the last attempt was successful, the retry timeout is very short, else its longer
            retryMilli = this.retryAttempts === 0 ? Math.floor(Math.random() * (3000 - 501 + 1)) + 501 : Math.floor(Math.random() * (30000 - 5000 + 1)) + 5000
            this.retryTimer = setTimeout(() => this.connect(this.url), retryMilli)
        }
        this.emit('CLOSE', {code: e.code || 1006, retryMilli: retryMilli})
    }

    onMsg(e){
        this.parseAndDispatch(e)
    }

    parseAndDispatch(event){
        let eventname = event.data.split(' ', 1)[0].toUpperCase(),
              payload = event.data.substring(eventname.length+1),
                 data = null
        try {
            data = JSON.parse(payload)
        } catch(ignored){
            data = payload
        }
        this.emit('DISPATCH', {data: data, event: eventname}) // Event is used to hook into all dispatched events
        this.emit(eventname, data)
    }

    send(eventname, data){
        const payload = (typeof data === 'string') ? data : JSON.stringify(data)
        if(this.isConnected()){
            this.socket.send(`${eventname} ${payload}`)
        } else {
            this.emit('ERR', 'notconnected')
        }
    }

}

export default ChatSource