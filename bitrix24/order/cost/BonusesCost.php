<?php

namespace common\bitrix24\order\cost;

class BonusesCost implements CalculatorInterface
{
    private int $bonuses;
    private float $discount = 0;

    public function __construct(int $bonuses)
    {
        $this->bonuses = max($bonuses, 0);
    }

    public function getCost(float $cost): float
    {
        $cost = max($cost, 0.0);

        if ($cost <= 0.0 || $this->bonuses <= 0) {
            return $cost;
        }

        $maxAllowedBonus = $cost * 0.5;
        $maxAllowedBonus = floor($maxAllowedBonus);

        if ($maxAllowedBonus < 1) {
            return $cost;
        }

        $this->discount = min($this->bonuses, $maxAllowedBonus);

        return $cost - $this->discount;
    }

    public function getDiscountValue()
    {
        return $this->discount;
    }

    public function getName(): string
    {
        return 'bonuses';
    }
}