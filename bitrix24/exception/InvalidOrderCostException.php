<?php

namespace common\bitrix24\exception;

use DomainException;

class InvalidOrderCostException extends DomainException {
    public function __construct(float $cost) {
        parent::__construct("Invalid order cost: {$cost}");
    }
}