/* global $, destiny */

import Chat from './chat.js';

class ChatAutoComplete {

    constructor(chat){

        this.minWordLength = 1;
        this.maxResults    = 10;
        this.buckets       = {};
        this.origVal       = null;
        this.searchResults = [];
        this.searchIndex   = -1;
        this.searchWord    = null;
        this.input         = chat.input;

        //HTMLInputElement.prototype.setSelectionRange
        if (!this.input || this.input.length === 0 || !this.input[0].setSelectionRange)
            return this;

        setInterval(this.expireUsers.bind(this), 60000); // 1 minute

        this.input.on('mousedown', this.resetSearch.bind(this));
        this.input.on('keydown', e => {
            if (e.which === 9) { // if TAB
                e.preventDefault();
                e.stopPropagation();
                if (this.searchResults.length <= 0) {
                    this.resetSearch();
                    this.searchSelectWord(e.shiftKey);
                }
                this.showAutoComplete();
            } else {
                // Cancel the search and continue the keydown
                this.resetSearch();
            }
        });
    }

    getBucketId(id){
        if (id.length === 0)
            return '';
        return id[0].toLowerCase();
    }

    addToBucket(data, weight, isemote, promoteTimestamp){
        let id = this.getBucketId(data);

        if(!this.buckets[id])
            this.buckets[id] = {};

        if (!this.buckets[id][data])
            this.buckets[id][data] = {
                data: data,
                weight: weight,
                isemote: !!isemote,
                promoted: promoteTimestamp
            };

        return this.buckets[id][data];
    }

    toggleNick(data, val){
        return val ? this.removeNick(data) : this.updateNick(data);
    }

    removeNick(data){
        let id = this.getBucketId(data);
        if(this.buckets[id] && this.buckets[id][data]){
            delete this.buckets[id][data];
            return true;
        }
        return false;
    }

    addEmote(emote){
        this.addToBucket(emote, 1, true, 0);
    }

    updateNick(nick){
        const weight = Date.now();
        const data = this.addToBucket(nick, weight, false, 0);
        data.weight = weight;
    }

    promoteNick(nick){
        const promoteTimestamp = Date.now();
        const data = this.addToBucket(nick, 1, false, promoteTimestamp);

        if (data.isemote)
            return this;

        data.promoted = promoteTimestamp;
    }

    getSearchWord(str, offset){
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
            startCaret: startCaret,
            isUserSearch: isUserSearch
        };
    }

    sortResults(a, b){
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
    }

    searchBuckets(str, limit, usernamesOnly){
        str = Chat.makeSafeForRegex(str);
        let res  = [],
            f    = new RegExp('^'+str, 'i'),
            data = this.buckets[this.getBucketId(str)] || {};

        for (let nick in data) {
            if (!data.hasOwnProperty(nick) || (usernamesOnly && data[nick].isemote))
                continue;

            if (f.test(nick))
                res.push(data[nick]);
        }

        res.sort(this.sortResults);
        return res.slice(0, limit);
    }

    expireUsers(){
        // every 10 minutes reset the promoted users so that emotes can be
        // ordered before the user again
        let tenminutesago = Date.now() - 600000;
        for (let i in this.buckets) {
            if (!this.buckets.hasOwnProperty(i))
                continue;

            for(let j in this.buckets[i]) {
                if (!this.buckets[i].hasOwnProperty(j))
                    continue;

                let data = this.buckets[i][j];
                if (!data.isemote && data.promoted <= tenminutesago)
                    data.promoted = 0;

                if (!data.isemote && data.weight <= tenminutesago)
                    data.weight = 1;
            }
        }
    }

    markLastComplete(){
        if(!this.lastComplete)
            return;

        let data = this.buckets[this.getBucketId(this.lastComplete)] || {};

        // should never happen, but just in case
        if (!data[this.lastComplete])
            return this.lastComplete = null;

        if (data[this.lastComplete].isemote) {
            // reset the promotion of users near the emote
            for(let j in data) {
                if (!data.hasOwnProperty(j))
                    continue;

                data[j].promoted = 0;
            }
            return this.lastComplete = null;
        }

        this.promoteNick(this.lastComplete);
        this.lastComplete = null;
    }

    resetSearch(){
        this.origVal       = null;
        this.searchResults = [];
        this.searchIndex   = -1;
        this.searchWord    = null;
    }

    searchSelectWord(forceUserSearch){
        let searchWord = this.getSearchWord(this.input.val(), this.input[0].selectionStart);
        if (searchWord.word.length >= this.minWordLength){
            this.searchWord    = searchWord;
            let isUserSearch   = forceUserSearch? true: this.searchWord.isUserSearch;
            this.searchResults = this.searchBuckets(this.searchWord.word, this.maxResults, isUserSearch);
            this.origVal       = this.input.val().toString();
        }
    }

    showAutoComplete(){
        this.searchIndex = this.searchIndex >= this.searchResults.length - 1 ? 0 : this.searchIndex + 1;
        let result = this.searchResults[this.searchIndex];
        if (!result || result.data === this.searchWord.word)
            return;

        this.lastComplete = result.data;
        let pre  = this.origVal.substr(0, this.searchWord.startCaret),
            post = this.origVal.substr(this.searchWord.startCaret + this.searchWord.word.length);

        // always add a space after our completion if there isn't one since people
        // would add one anyway
        if (post[0] !== ' ' || post.length === 0)
            post = ' ' + post;

        this.input.focus().val(pre + result.data + post);

        // Move the caret to the end of the replacement string + 1 for the space
        this.input[0].setSelectionRange(pre.length + result.data.length + 1, pre.length + result.data.length + 1);
    }

}

export default ChatAutoComplete;