<?php

namespace Destiny\Commerce;

abstract class SubscriptionStatus {
    
    /**
     * Used for when a new sub is created before the order has cleared
     */
    const _NEW = 'New';
    /**
     * Active and enabled subscription
     */
    const ACTIVE = 'Active';
    /**
     * When a sub is waiting for the order to clear automatically
     */
    const PENDING = 'Pending';
    /**
     * When the sub end date has passed
     */
    const EXPIRED = 'Expired';
    /**
     * A cancelled subscription
     */
    const CANCELLED = 'Cancelled';
    /**
     * When an error occurred during subscription
     */
    const ERROR = 'Error';
}