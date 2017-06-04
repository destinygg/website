
class Feature {

    constructor(id, name, label){
        this.id = id;
        this.name = name;
        this.label = label;
    }

    toString(){
        return this.id;
    }

    equals(str){
        return this.id.localeCompare(str) === 0;
    }

}

const features = {
    PROTECTED     : new Feature( 'protected',    'PROTECTED',        'Protected'            ),
    SUBSCRIBER    : new Feature( 'subscriber',   'SUBSCRIBER',       'Subscriber'           ),
    SUBSCRIBERT0  : new Feature( 'flair9',       'SUBSCRIBERT0',     'Twitch subscriber'    ),
    SUBSCRIBERT1  : new Feature( 'flair13',      'SUBSCRIBERT1',     'Subscriber (T1)'      ),
    SUBSCRIBERT2  : new Feature( 'flair1',       'SUBSCRIBERT2',     'Subscriber (T2)'      ),
    SUBSCRIBERT3  : new Feature( 'flair3',       'SUBSCRIBERT3',     'Subscriber (T3)'      ),
    SUBSCRIBERT4  : new Feature( 'flair8',       'SUBSCRIBERT4',     'Subscriber (T4)'      ),
    VIP           : new Feature( 'vip',          'VIP',              'VIP'                  ),
    MODERATOR     : new Feature( 'moderator',    'MODERATOR',        'Moderator'            ),
    ADMIN         : new Feature( 'admin',        'ADMIN',            'Admin'                ),
    BROADCASTER   : new Feature( 'flair12',      'BROADCASTER',      'Broadcaster'          ),
    BOT           : new Feature( 'bot',          'BOT',              'Bot'                  ),
    BOT2          : new Feature( 'flair11',      'BOT2',             'Bot2'                 ),
    NOTABLE       : new Feature( 'flair2',       'NOTABLE',          'Notable'              ),
    TRUSTED       : new Feature( 'flair4',       'TRUSTED',          'Trusted'              ),
    CONTRIBUTOR   : new Feature( 'flair5',       'CONTRIBUTOR',      'Contributor'          ),
    COMPCHALLENGE : new Feature( 'flair6',       'COMPCHALLENGE',    'Composition winner'   ),
    EVE           : new Feature( 'flair7',       'EVE',              'EVE'                  ),
    SC2           : new Feature( 'flair10',      'SC2',              'Starcraft 2'          )
};

const mapping = new Map([
    ['protected', features.PROTECTED],
    ['subscriber', features.SUBSCRIBER],
    ['flair9', features.SUBSCRIBERT0],
    ['flair1', features.SUBSCRIBERT2],
    ['flair13', features.SUBSCRIBERT1],
    ['flair3', features.SUBSCRIBERT3],
    ['flair8', features.SUBSCRIBERT4],
    ['vip', features.VIP],
    ['moderator', features.MODERATOR],
    ['admin', features.ADMIN],
    ['flair12', features.BROADCASTER],
    ['bot', features.BOT],
    ['flair11', features.BOT2],
    ['flair2', features.NOTABLE],
    ['flair4', features.TRUSTED],
    ['flair5', features.CONTRIBUTOR],
    ['flair6', features.COMPCHALLENGE],
    ['flair7', features.EVE],
    ['flair10', features.SC2]
]);

features.valueOf = function(str){
    return mapping.get(str.toLowerCase()) || null;
};

export default features