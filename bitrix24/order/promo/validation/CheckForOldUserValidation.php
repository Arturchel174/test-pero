<?php

namespace common\bitrix24\order\promo\validation;

use common\bitrix24\order\type\PromoCodeType;


class CheckForOldUserValidation implements ValidationInterface
{
    private int $user_secure_status;
    const TYPE_SECURE_OLD_USERS = 3;

    public function __construct($user_secure_status)
    {
        $this->user_secure_status = $user_secure_status;
    }
    public function checkError(PromoCodeType $promoCodeType)
    {
        if ($this->isPromoForOldUser($promoCodeType->getSecureStatus())
            && $this->isNotOldUser($this->user_secure_status)) {
            return 'Данный промокод только для старых пользователей, попробуйте другой!';
        }

        return false;
    }

    private static function isPromoForOldUser($promo_code_secure_status): bool
    {
        return $promo_code_secure_status === self::TYPE_SECURE_OLD_USERS;
    }

    private static function isNotOldUser($user_secure_status): bool
    {
        return $user_secure_status !== self::TYPE_SECURE_OLD_USERS;
    }
}