<?php
namespace Destiny\Common\User;

class UserRole {
    // an authenticated user
    const USER = 'USER';
    // has subscription
    const SUBSCRIBER = 'SUBSCRIBER';
    // can access website administration
    const ADMIN = 'ADMIN';
    // has access to users, bans etc
    const MODERATOR = 'MODERATOR';
    // can view the financial graphs and info
    const FINANCE = 'FINANCE';
    // used for the streamlabs alerts, should only be the broadcaster
    const STREAMLABS = 'STREAMLABS';
    // used for the streamelements alerts, should only be the broadcaster
    const STREAMELEMENTS = 'STREAMELEMENTS';
    // can add, update, remove emotes
    const EMOTES = 'EMOTES';
    // can add, update, remove flairs
    const FLAIRS = 'FLAIRS';

}