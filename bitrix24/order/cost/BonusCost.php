<?php

namespace common\bitrix24\order\cost;

class BonusCost implements CalculatorInterface
{
    private int $bonuses;

    public function __construct(int $bonuses)
    {
        $this->bonuses = max($bonuses, 0); // Гарантируем неотрицательные бонусы
    }

    public function getCost(float $cost): float
    {
        $cost = max($cost, 0.0); // Гарантируем неотрицательную стоимость

        if ($cost <= 0.0 || $this->bonuses <= 0) {
            return $cost;
        }

        $maxAllowedBonus = $cost * 0.5;
        $appliedBonus = min($this->bonuses, $maxAllowedBonus);

        return $cost - $appliedBonus;
    }
}