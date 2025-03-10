<?php

namespace common\bitrix24\order\promo\validation;

use common\bitrix24\order\type\PromoCodeType;
use common\bitrix24\order\type\UserType;


class CheckForNewUserValidation implements ValidationInterface
{
    private int $user_secure_status;
    private int $TYPE_SECURE_NEW_USERS;


    public function __construct($user_secure_status, $TYPE_SECURE_NEW_USERS)
    {
        $this->user_secure_status = $user_secure_status;
        $this->TYPE_SECURE_NEW_USERS = $TYPE_SECURE_NEW_USERS;
    }
    public function checkError(PromoCodeType $promoCodeType)
    {
        if ($this->isPromoForNewUser($promoCodeType->getSecureStatus())
            && $this->isNotNewUser($this->user_secure_status)) {
            return 'Данный промокод только для новых пользователей, попробуйте другой!';
        }

        return false;
    }

    private function isPromoForNewUser($promo_code_secure_status): bool
    {
        return $promo_code_secure_status === $this->TYPE_SECURE_NEW_USERS;
    }

    private function isNotNewUser($user_secure_status): bool
    {
        return $user_secure_status !== $this->TYPE_SECURE_NEW_USERS;
    }
}