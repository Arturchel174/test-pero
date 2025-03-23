<?php

namespace common\bitrix24\order\factory;

use common\bitrix24\order\storage\DealOrderStorage;
use common\bitrix24\order\type\OrderType;
use common\types\DealBx24;
use common\bitrix24\order\type\UserType;
use common\bitrix24\order\type\RegionType;

class OrderTypeFactory implements OrderTypeFactoryInterface
{
    public function create(
        DealBx24 $deal,
        UserType $userType,
        RegionType $regionType,
        array $discountValues = []
    ): OrderType {
        return new OrderType(
            new DealOrderStorage(
                $deal,
                $userType->getUserId(),
                $regionType->getCurrentRegionId(),
                $discountValues
            )
        );
    }
}