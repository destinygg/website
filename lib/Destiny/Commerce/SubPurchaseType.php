<?php

namespace Destiny\Commerce;

abstract class SubPurchaseType {
    /**
     * The user is buying a sub for themselves.
     */
    const _SELF = 'self';

    /**
     * The user is gifting a sub to a specific user.
     */
    const DIRECT_GIFT = 'direct-gift';

    /**
     * The user is buying one or more subs to distribute randomly.
     */
    const MASS_GIFT = 'mass-gift';
}