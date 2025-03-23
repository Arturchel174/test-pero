<?php

namespace common\bitrix24\order\service\discount\factory;

use common\bitrix24\order\service\discount\context\DiscountContext;

interface DiscountStrategyFactoryInterface
{
    public function createStrategies(DiscountContext $context): array;
}