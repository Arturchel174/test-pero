<?php

namespace common\bitrix24\order\promo\validation;

use common\bitrix24\order\type\PromoCodeType;

class PersonalValidation extends MinePromoCodeValidation implements ValidationInterface
{
    private string $username;
    public function __construct($user_id, string $username)
    {
        $this->username = $username;
        parent::__construct($user_id);
    }

    public function checkError(PromoCodeType $promoCodeType)
    {
        if(static::isPersonalTypePromoCode($promoCodeType->getTypeName())){
            if($this->username !== $promoCodeType->getUsername()){
                return 'Это не ваш персональный промокод!';
            }
        }

        return false;
    }
}