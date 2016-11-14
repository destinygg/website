import ChatFormatter from './formatter.js';

const el = document.createElement('div');

class HtmlTextFormatter extends ChatFormatter {

    constructor(){
        super();
    }

    format(str){
        el.textContent = str;
        return el.innerHTML;
    }

}

export default HtmlTextFormatter;