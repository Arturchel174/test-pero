<?php

namespace common\bitrix24\order\factory;

use common\bitrix24\order\type\OrderType;
use common\bitrix24\order\type\RegionType;
use common\bitrix24\order\type\UserType;
use common\types\DealBx24;

interface OrderTypeFactoryInterface
{
    public function create(
        DealBx24 $deal,
        UserType $userType,
        RegionType $regionType,
        array $discountValues = []
    ): OrderType;
}