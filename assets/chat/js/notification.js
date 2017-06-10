/* global window, document, navigator */
/*
Copyright (C) 2013 Hendrik Beskow
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to
deal in the Software without restriction, including without limitation the
rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
sell copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
;(function(win, doc, nav) {
    if ( /* Safari 6, Firefox 22 */ !('Notification' in win && 'permission' in win.Notification && 'requestPermission' in win.Notification)) {
      const PERMISSION_DEFAULT = 'default',
            PERMISSION_GRANTED = 'granted',
            PERMISSION_DENIED = 'denied',
            PERMISSION = [PERMISSION_GRANTED, PERMISSION_DEFAULT, PERMISSION_DENIED],
            isString = function(value) {
                return (value && (value).constructor === String);
            },
            isFunction = function(value) {
                return (value && (value).constructor === Function);
            },
            isObject = function(value) {
                return (value && (value).constructor === Object);
            },
            noop = function() {};

        function checkPermission() {
            let permission;
            if ( /* Chrome, Firefox < 22 && ff-html5notifications */ !! ('webkitNotifications' in win && 'checkPermission' in win.webkitNotifications)) {
                permission = PERMISSION[win.webkitNotifications.checkPermission()];
            } else if ( /* Firefox Mobile */ 'mozNotification' in nav) {
                permission = PERMISSION_GRANTED;
            } else {
                permission = (localStorage.getItem('notifications') === null) ? PERMISSION_DEFAULT : localStorage.getItem('notifications');
            }
            return permission;
        }

        function requestPermission(callback) {
            let callbackFunction = isFunction(callback) ? callback : noop;
            if ( /* Chrome, Firefox < 22 && ff-html5notifications */ !! ('webkitNotifications' in win && 'requestPermission' in win.webkitNotifications)) {
                win.webkitNotifications.requestPermission(callbackFunction);
            } else {
                if (checkPermission() === PERMISSION_DEFAULT) {
                    if (confirm('Do you want to allow ' + doc.domain + ' to display Notifications?')) {
                        localStorage.setItem('notifications', PERMISSION_GRANTED);
                        Notification.permission = PERMISSION_GRANTED;
                    } else {
                        localStorage.setItem('notifications', PERMISSION_DENIED);
                        Notification.permission = PERMISSION_DENIED;
                    }
                }
                callbackFunction();
            }
        }

        function Notification(title, params) {
            let notification;
            if (isString(title) &&
                isObject(params) &&
                checkPermission() === PERMISSION_GRANTED) {
                if ( /* Firefox Mobile */ 'mozNotification' in nav) {
                    notification = nav.mozNotification.createNotification(title, params.body, params.icon);
                    if (isFunction(params.onclick)) {
                        notification.onclick = params.onclick;
                    }
                    if (isFunction(params.onclose)) {
                        notification.onclose = params.onclose;
                    }
                    notification.show();
                } else if ( /* Chrome, Firefox < 22 && ff-html5notifications */ 'webkitNotifications' in win && 'createNotification' in win.webkitNotifications) {
                    notification = win.webkitNotifications.createNotification(params.icon, title, params.body);
                    if (isFunction(params.onclick)) {
                        notification.onclick = params.onclick;
                    }
                    if (isFunction(params.onshow)) {
                        notification.onshow = params.onshow;
                    }
                    if (isFunction(params.onerror)) {
                        notification.onerror = params.onerror;
                    }
                    if (isFunction(params.onclose)) {
                        notification.onclose = params.onclose;
                    }
                    notification.show();
                } else {
                    // Your custom code goes here
                }
            }
            return notification;
        }

        if (!('Notification' in win)) {
            Notification.requestPermission = requestPermission;
            win.Notification = Notification;
        }

        if ( !! ('webkitNotifications' in win && 'checkPermission' in win.webkitNotifications)) {
            Object.defineProperty(win.Notification, 'permission', {
                get: function() {
                    return PERMISSION[win.webkitNotifications.checkPermission()];
                }
            });
        } else {
            Notification.permission = checkPermission();
        }

        if (!('requestPermission' in win.Notification)) {
            win.Notification.requestPermission = requestPermission;
        }
    }
}(window, document, navigator));