/* global $, destiny */

import Chat from "./chat";
import {KEYCODES,getKeyCode} from "./const";

const getBucketId = id => {
    return (id.match(/[\S]/)[0] || '_').toLowerCase();
};
const sortResults = (a, b) => {
    if(!a || !b)
        return 0;

    // order promoted things first
    if (a.promoted !== b.promoted)
        return a.promoted > b.promoted? -1: 1;

    // order emotes second
    if (a.isemote !== b.isemote)
        return a.isemote && !b.isemote? -1: 1;

    // order according to recency third
    if (a.weight !== b.weight)
        return a.weight > b.weight? -1: 1;

    // order lexically fourth
    a = a.data.toLowerCase();
    b = b.data.toLowerCase();

    if (a === b)
        return 0;

    return a > b? 1: -1;
};
const getSearchCriteria = (str, offset) => {
    let pre          = str.substring(0, offset),
        post         = str.substring(offset),
        startCaret   = pre.lastIndexOf(' ') + 1,
        endCaret     = post.indexOf(' '),
        isUserSearch = false;

    if (startCaret > 0)
        pre = pre.substring(startCaret);

    if (endCaret > -1)
        post = post.substring(0, endCaret);

    // Ignore the first char as part of the search and flag as a user only search
    if(pre.lastIndexOf('@') === 0){
        startCaret++;
        pre = pre.substring(1);
        isUserSearch = true;
    }

    return {
        word: pre + post,
        pre: pre,
        post: post,
        startCaret: startCaret,
        isUserSearch: isUserSearch,
        orig: str
    };
};

class ChatAutoComplete {

    constructor(){
        this.minWordLength = 1;
        this.maxResults = 20;
        this.buckets = new Map();
        this.searchResults = [];
        this.searchCriteria = null;
        this.selectedIndex = -1;
        this.input = null;
        this.timeout = null;
        this.ui = $(`<div id="chat-auto-complete"></div>`);
        this.container = null;
    }

    redrawHelpers(){
        const elements = this.searchResults.map((res, k) => `<li data-index="${k}">${res.data}</li>`);
        this.container = $(`<ul>${elements.join('')}</ul>`);
        this.ui.detach()
            .empty()
            .append(this.container)
            .toggleClass('active', elements.length > 0);
        this.input.before(this.ui);
        this.updateHelpers();
    }

    updateHelpers() {
        this.ui.find(`li.active`).removeClass('active');
        this.ui.find(`li[data-index="${this.selectedIndex}"]`).addClass('active');
        const offset = this.container.position().left,
            maxwidth = this.ui.width(),
                  li = this.ui.find(`li`).get(),
                curr = this.ui.find(`li.active`);
        if (curr.length > 0) {
            $(li[this.selectedIndex + 3]).each((i, e) => {
                const right = ($(e).position().left + offset) + $(e).outerWidth();
                if(right > maxwidth)
                    this.container.css('left', offset + maxwidth - right);
            });
            $(li[Math.max(0, this.selectedIndex - 2)]).each((i, e) => {
                const left = $(e).position().left + offset;
                if(left < 0)
                    this.container.css('left', $(e).position().left);
            });
        }
    }

    bind(chat){
        this.input = chat.input;

        // Mouse down, if there is no text selection search the word from where the caret is
        this.input.on('mouseup', () => {
            const offset = this.input[0].selectionStart;
            if(offset !== this.input[0].selectionEnd){
                this.reset();
                this.redrawHelpers();
            } else {
                const needle = this.input.val().toString();
                this.search(needle, offset);
            }
        });

        // Key down for any key, but we cannot get the charCode from it (like keypress).
        let originval = '';
        this.input.on('keydown', e => {
            originval = this.input.val().toString();
            switch (getKeyCode(e)) {
                case KEYCODES.TAB:
                    if(this.searchResults.length > 0) {
                        this.selectResult(this.selectedIndex >= this.searchResults.length - 1 ? 0 : this.selectedIndex + 1);
                        this.updateHelpers();
                    }
                    e.preventDefault();
                    e.stopPropagation();
                    break;
            }
        });

        // Key press of characters that actually input into the field
        this.input.on('keypress', e => {
            const char = String.fromCharCode(getKeyCode(e)) || '';
            switch (getKeyCode(e)) {
                case KEYCODES.ENTER:
                case KEYCODES.TAB:
                    break;
                default:
                    if (char.length > 0) {
                        this.promoteIfSelected();
                        const str = this.input.val().toString(),
                            offset = this.input[0].selectionStart,
                            pre = str.substring(0, offset),
                            post = str.substring(offset);
                        const needle = pre + char + post;
                        this.search(needle, offset);
                    }
                    break;
            }
        });

        // Key up, we handle things like backspace if the keypress never found a char.
        this.input.on('keyup', e => {
            const needle = this.input.val().toString();
            switch (getKeyCode(e)) {
                case KEYCODES.TAB:
                    break;
                case KEYCODES.ENTER:
                    this.reset();
                    this.redrawHelpers();
                    break;
                default:
                    if (needle !== originval) {
                        const offset = this.input[0].selectionStart;
                        this.search(needle, offset);
                    }
                    if(needle === '') {
                        this.reset();
                        this.redrawHelpers();
                    }
                    break;
            }
            originval = '';
        });

        this.ui.on('click', 'li', e => {
            this.selectResult(parseInt(e.currentTarget.getAttribute('data-index')));
            this.redrawHelpers();
        });

        setInterval(this.expireUsers.bind(this), 60000); // 1 minute
    }

    search(needle, offset, userSearch=false){
        this.reset();
        const criteria = getSearchCriteria(needle, offset);
        if(userSearch) criteria.isUserSearch = true;
        this.searchCriteria = criteria;
        this.searchResults = this.find(criteria);
        this.redrawHelpers();
    }

    reset(){
        this.searchCriteria = null;
        this.searchResults = [];
        this.selectedIndex = -1;
    }

    add(str, isemote=false, weight=1, promote=0){
        const id = getBucketId(str);
        const bucket = this.buckets.get(id) || new Map();
        const data = bucket.get(str) || {
            data: str,
            weight: weight,
            isemote: isemote,
            promoted: promote
        };
        bucket.set(str, data);
        this.buckets.set(id, bucket);
        return data;
    }

    remove(str){
        const id = getBucketId(str);
        const bucket = this.buckets.get(id);
        if(bucket && bucket.has(str)) {
            bucket.delete(str);
            return true;
        }
        return false;
    }

    weight(str, weight=Date.now(), promote=0){
        const data = this.add(str, false, weight, promote);
        data.weight = weight;
    }

    find(criteria){
        if(criteria.word.length >= this.minWordLength) {
            const id = getBucketId(criteria.word);
            const bucket = this.buckets.get(id) || new Map();
            const regex = new RegExp('^' + Chat.makeSafeForRegex(criteria.pre), 'i');
            return [...bucket.values()]
                .filter(a => a.data.toLowerCase() !== criteria.word.toLowerCase())
                .filter(a => (!criteria.isUserSearch || !a.isemote) && regex.test(a.data))
                .sort(sortResults)
                .slice(0, this.maxResults);
        }
        return [];
    }

    selectResult(index){
        this.selectedIndex = Math.min(index, this.searchResults.length-1);
        const result = this.searchResults[this.selectedIndex];
        let pre = this.searchCriteria.orig.substr(0, this.searchCriteria.startCaret),
            post = this.searchCriteria.orig.substr(this.searchCriteria.startCaret + this.searchCriteria.word.length);

        // always add a space after our completion if there isn't one since people
        // would add one anyway
        if (post[0] !== ' ' || post.length === 0)
            post = ' ' + post;

        this.input.focus().val(pre + result.data + post);

        // Move the caret to the end of the replacement string + 1 for the space
        this.input[0].setSelectionRange(pre.length + result.data.length + 1, pre.length + result.data.length + 1);
        this.selectedIndex = index;
    }

    promoteIfSelected(){
        if(this.selectedIndex >= 0) {
            const result = this.searchResults[this.selectedIndex];
            if(result) {
                result.promoted = Date.now();
                if(result.isemote) {
                    const bucket = this.buckets.get(getBucketId(result.data)) || new Map();
                    [...bucket.values()]
                        .filter(a => !a.isemote)
                        .forEach(a => a.promoted = 0);
                }
            }
        }
    }

    expireUsers(){
        // every 10 minutes reset the promoted users so that emotes can be
        // ordered before the user again
        let tenminutesago = Date.now() - 600000;
        this.buckets.forEach(bucket => {
            [...bucket.values()]
                .filter(a => !a.isemote)
                .forEach(a => {
                    if (a.promoted <= tenminutesago)
                        a.promoted = 0;
                    if (a.weight <= tenminutesago)
                        a.weight = 1;
                });
        });
    }

}

export default ChatAutoComplete;