<?php

namespace common\bitrix24\order\cost;

use common\bitrix24\order\type\ProgramType;

interface CalculatorInterface
{
    /**
     *@param float $cost
     * @return float
     */
    public function  getCost(float $cost): float;
    public function getDiscountValue();
    public function getName(): string;
}