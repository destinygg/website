/* global window */

class Logger {
    constructor(context){
        this.console = window.console;
        this.context = context;
    }
    static make(context){
        return new Logger(`[${context.constructor.name}]`);
    }
    debug(...args){
        if(window.destiny.loglevel >= 2) this.console.debug(this.context, ...args);
    }
    log(...args){
        if(window.destiny.loglevel >= 1) this.console.log(this.context, ...args);
    }
    info(...args){
        if(window.destiny.loglevel >= 0) this.console.info(this.context, ...args);
    }
    error(...args){
        if(window.destiny.loglevel >= 0) this.console.error(this.context, ...args);
    }
}

export default Logger;