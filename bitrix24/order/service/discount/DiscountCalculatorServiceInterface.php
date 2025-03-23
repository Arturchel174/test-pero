<?php

namespace common\bitrix24\order\service\discount;

use common\bitrix24\order\service\discount\context\DiscountContext;

interface DiscountCalculatorServiceInterface
{
    public function calculate(DiscountContext $context): float;
    public function getDiscountValues(): array;
}