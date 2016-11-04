/* global $, destiny */

import ChatFormatter from '../formatter.js';

class GreenTextFormatter extends ChatFormatter {

    format(str, user){
        if(user && str.indexOf('&gt;') === 0){
            if(user.hasAnyFeatures(
                    destiny.UserFeatures.SUBSCRIBERT3,
                    destiny.UserFeatures.SUBSCRIBERT4,
                    destiny.UserFeatures.SUBSCRIBERT2,
                    destiny.UserFeatures.ADMIN,
                    destiny.UserFeatures.MODERATOR
                ))
                str = `<span class="greentext">${str}</span>`;
        }
        return str;
    }

}

export default GreenTextFormatter;