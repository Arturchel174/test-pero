<?php

namespace common\bitrix24\order\service\discount;

use common\bitrix24\order\cost\CalculatorInterface;
use common\bitrix24\order\service\discount\context\DiscountContext;
use common\bitrix24\order\service\discount\factory\DiscountStrategyFactoryInterface;

class DiscountCalculatorService implements DiscountCalculatorServiceInterface
{
    private array $discountValues = [];
    private DiscountStrategyFactoryInterface $strategyFactory;

    public function __construct(
        DiscountStrategyFactoryInterface $strategyFactory
    ) {
        $this->strategyFactory = $strategyFactory;
    }

    public function calculate(DiscountContext $context): float
    {
        $cost = $context->program->getPrice();

        foreach ($this->strategyFactory->createStrategies($context) as $strategy) {
            /**
             *@var CalculatorInterface $strategy
             */
            $cost = $strategy->getCost($cost);
            $this->discountValues[$strategy->getName()] = $strategy->getDiscountValue();
        }

        return $cost;
    }

    public function getDiscountValues(): array
    {
        return $this->discountValues;
    }
}