<?php

namespace common\bitrix24\order\promo\validation;

use common\bitrix24\order\type\PromoCodeType;
use common\bitrix24\order\type\UserType;

interface ValidationInterface
{
    /**
     * @param PromoCodeType $promoCodeType
     */
    public function checkError(PromoCodeType $promoCodeType);
}