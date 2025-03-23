<?php

namespace common\bitrix24\order\service;

use common\bitrix24\order\cost\CalculatorInterface;
use common\bitrix24\order\promo\validation\CheckForNewUserValidation;
use common\bitrix24\order\promo\validation\CheckForOldUserValidation;
use common\bitrix24\order\promo\validation\LifeTimeValidation;
use common\bitrix24\order\promo\validation\LimitationDayPersonalValidation;
use common\bitrix24\order\promo\validation\MinePromoCodeValidation;
use common\bitrix24\order\promo\validation\PersonalValidation;
use common\bitrix24\order\promo\validation\UsageValidation;
use common\bitrix24\order\promo\validation\ValidationPromoCode;
use common\bitrix24\order\type\ProgramType;
use common\bitrix24\order\type\PromoCodeType;
use common\bitrix24\order\type\UserType;


class CreateOrderService
{
    private UserType $userType;
    private ?PromoCodeType $promoCodeType = null;

    public function __construct(UserType $userType, PromoCodeType $promoCodeType = null)
    {
        $this->userType = $userType;

        if($this->userType->isNotGuest()){
            $this->promoCodeType = $promoCodeType;
        }
    }

    public function validationPromoCode()
    {
        if($this->promoCodeType !== null && $this->promoCodeType->isExitPromoCode()){
            $validationPromoCode = new ValidationPromoCode(
                new CheckForNewUserValidation($this->userType->getSecureStatus(), $this->userType::TYPE_SECURE_NEW_USERS),
                new CheckForOldUserValidation($this->userType->getSecureStatus()),
                new LifeTimeValidation(),
                new LimitationDayPersonalValidation($this->userType->getUserId(), 14),
                new MinePromoCodeValidation($this->userType->getUserId()),
                new PersonalValidation($this->userType->getUserId(), $this->userType->getUsername()),
                new UsageValidation($this->userType->getUserId()),
            );

            $error = $validationPromoCode->checkError($this->promoCodeType);

            if($error !== false){
                throw new \RuntimeException($error);
            }

            $this->promoCodeType->setFlagValidate(true);
        }
    }

    public function setPromoCodeType(PromoCodeType $promoCodeType)
    {
        $this->promoCodeType = $promoCodeType;
    }
}