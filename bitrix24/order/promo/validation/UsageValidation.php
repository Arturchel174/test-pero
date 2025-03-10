<?php

namespace common\bitrix24\order\promo\validation;

use backend\models\UsagePromoCodes;
use common\bitrix24\order\type\PromoCodeType;

class UsageValidation implements ValidationInterface
{
    private int $user_id;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    public function checkError(PromoCodeType $promoCodeType)
    {
        $modelUsagePromoCodes = new UsagePromoCodes([
            'promo_code_id' => $promoCodeType->getPromoCodeId(),
            'user_id' => $this->user_id,
            'type_id' => $promoCodeType->getTypeId(),
        ]);

        if ($modelUsagePromoCodes->checkUsagePromoCode()) {
            return 'Вы уже использовали данный промокод!';
        }

        return false;
    }
}