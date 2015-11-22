<?php
namespace Destiny\PayPal;

abstract class PaymentProfileStatus {

    const _NEW = 'New';
    const ERROR = 'Error';
    const ACTIVE_PROFILE = 'ActiveProfile';
    const CANCELLED_PROFILE = 'CancelledProfile';
    const FAILED = 'Failed';
    const SKIPPED = 'Skipped';

}