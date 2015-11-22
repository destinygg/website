<?php
namespace Destiny\Commerce;

abstract class PaymentStatus {
    
    const _NEW = 'New';
    const ACTIVE = 'Active';
    const PENDING = 'Pending';
    const COMPLETED = 'Completed';
    const CANCELLED = 'Cancelled';
    const ERROR = 'Error';
    const FAILED = 'Failed';
    const SKIPPED = 'Skipped';

}