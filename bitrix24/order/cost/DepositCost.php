<?php

namespace common\bitrix24\order\cost;

class DepositCost implements CalculatorInterface
{
    private int $deposit;
    private float $discount = 0;

    public function __construct(int $deposit)
    {
        $this->deposit = max($deposit, 0);
    }

    public function getCost(float $cost): float
    {
        $cost = max($cost, 0.0);

        if ($this->deposit <= 0 || $cost <= 0) {
            return $cost;
        }

        $maxToApply = $cost - 1;
        $this->discount = min($this->deposit, $maxToApply);

        $cost -= $this->discount;

        return $cost;
    }

    public function getDiscountValue()
    {
        return $this->discount;
    }

    public function getName(): string
    {
        return 'deposit';
    }
}