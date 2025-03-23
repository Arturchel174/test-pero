<?php

namespace common\bitrix24\order\service\deal;

use common\bitrix24\order\type\DealType;
use common\types\DealBx24;
use common\bitrix24\order\type\{ProgramType, RegionType, UserType, PromoCodeType};

interface DealTypeCreatorInterface {
    public function create(
        DealBx24 $deal,
        ProgramType $programType,
        UserType $userType,
        ?PromoCodeType $promoCodeType
    ): DealType;

    public function configure(
        DealType $dealType,
        RegionType $regionType,
        UserType $userType
    ): void;
}