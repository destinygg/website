/* global $, destiny */

import ChatFormatter from './formatter.js';
import UserFeatures from '../features.js';

class GreenTextFormatter extends ChatFormatter {

    format(str, user){
        if(user && str.indexOf('&gt;') === 0){
            if(user.hasAnyFeatures(
                    UserFeatures.SUBSCRIBERT3,
                    UserFeatures.SUBSCRIBERT4,
                    UserFeatures.SUBSCRIBERT2,
                    UserFeatures.ADMIN,
                    UserFeatures.MODERATOR
                ))
                str = `<span class="greentext">${str}</span>`;
        }
        return str;
    }

}

export default GreenTextFormatter;