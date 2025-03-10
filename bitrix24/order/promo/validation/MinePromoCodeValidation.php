<?php

namespace common\bitrix24\order\promo\validation;

use common\bitrix24\order\type\PromoCodeType;

class MinePromoCodeValidation implements ValidationInterface
{
    private int $user_id;
    const TYPE_PROMO_CODE_PERSONAL = 'personal';

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    public function checkError(PromoCodeType $promoCodeType)
    {
        if (!self::isPersonalTypePromoCode($promoCodeType->getTypeName())
            && self::isMinePromoCode($this->user_id, $promoCodeType->getUserId())) {
            return 'Вы не можете использовать свой промокод!';
        }

        return false;
    }

    protected static function isPersonalTypePromoCode($type): bool
    {
        $is_personal = strrpos($type, self::TYPE_PROMO_CODE_PERSONAL);
        return $is_personal !== false;
    }

    private static function isMinePromoCode($user_id, $promo_code_user_id): bool
    {
        if(!isset($user_id)) return false;
        return $user_id === $promo_code_user_id;
    }
}