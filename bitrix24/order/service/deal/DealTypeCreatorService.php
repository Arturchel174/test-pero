<?php

namespace common\bitrix24\order\service\deal;

use common\bitrix24\order\storage\PostDealStorage;
use common\bitrix24\order\type\DealType;
use common\bitrix24\order\type\ProgramType;
use common\bitrix24\order\type\PromoCodeType;
use common\bitrix24\order\type\RegionType;
use common\bitrix24\order\type\UserType;
use common\types\DealBx24;

class DealTypeCreatorService implements DealTypeCreatorInterface
{
    public function create(
        DealBx24 $deal,
        ProgramType $programType,
        UserType $userType,
        ?PromoCodeType $promoCodeType
    ): DealType {
        return new DealType(
            new PostDealStorage(
                $deal,
                $programType,
                $userType,
                $promoCodeType
            )
        );
    }

    public function configure(
        DealType $dealType,
        RegionType $regionType,
        UserType $userType
    ): void {
        $dealType->createTitle();
        $dealType->setCategoryId($regionType->getStatusCategory());
        $dealType->setStageId($regionType->getDealStatusNew());
        $dealType->setCityId($regionType->getDealCityId());
        $dealType->setPayInfo(DealBx24::ORDER_NO_PAID_VALUE);

        if ($userType->isNewUser()) {
            $dealType->addTextForTitle(DealType::FIRST_ORDER_TEXT);
        }
    }
}