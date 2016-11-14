/* global window */

const localStorage = window.localStorage || {};
const JSON = window.JSON;

class ChatStore {

    static write(name, obj){
        let str = '';
        try{ str = JSON.stringify((obj instanceof Map || obj instanceof Set) ? [...obj] : obj); } catch(ignored){}
        localStorage.setItem(name, str);
    }

    static read(name){
        let data = null;
        try{ data = JSON.parse(localStorage.getItem(name)); } catch(ignored){}
        return data;
    }

}

export default ChatStore;