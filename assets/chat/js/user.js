class ChatUser {

    constructor(args){
        this.nick     = args.nick || '';
        this.username = args.nick || '';
        this.features = args.features || [];
    }

    hasAnyFeatures(...args){
        for (const element of args) {
            if(this.features.indexOf(element) !== -1)
                return true;
        }
        return false;
    }

    hasFeature(feature){
        return this.hasAnyFeatures(feature);
    }
}

module.exports = ChatUser;