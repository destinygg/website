<?php
namespace Destiny\Common\User;

abstract class UserRole {
    
    const USER = 'USER';             // an authenticated user
    const SUBSCRIBER = 'SUBSCRIBER'; // has subscription
    const ADMIN = 'ADMIN';           // can access website administration
    const MODERATOR = 'MODERATOR';   // has access to users, bans etc
    const FINANCE = 'FINANCE';       // can view the financial graphs and info
    const STREAMLABS = 'STREAMLABS'; // used for the streamlabs alerts, should only be the broadcaster
    const EMOTES = 'EMOTES';         // can add, update, remove emotes
    const FLAIRS = 'FLAIRS';         // can add, update, remove flairs

    public static $ASSIGNABLE = [
        self::ADMIN,
        self::MODERATOR,
        self::FINANCE,
        self::STREAMLABS,
        self::EMOTES,
        self::FLAIRS
    ];

}