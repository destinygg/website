<?php
namespace Destiny\Commerce;

abstract class OrderStatus {
    
    const _NEW = 'New';
    const ERROR = 'Error';
    const COMPLETED = 'Completed';
    const PENDING = 'Pending';

}