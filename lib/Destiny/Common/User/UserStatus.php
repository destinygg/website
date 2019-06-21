<?php
namespace Destiny\Common\User;

class UserStatus {

    const ACTIVE = 'Active';        // a normal active user
    const DELETED = 'Deleted';      // a user that requested their account deleted
    const REDACTED = 'Redacted';    // a user which has been deleted / sanitized

}