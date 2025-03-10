<?php

namespace common\bitrix24\order\promo\validation;

use common\bitrix24\order\type\PromoCodeType;
use DateTimeImmutable;

class LifeTimeValidation implements ValidationInterface
{
    /**
     * @throws \Exception
     */
    public function checkError(PromoCodeType $promoCodeType)
    {
        $closeDate = new DateTimeImmutable($promoCodeType->getCloseDate());
        $nowDate = new DateTimeImmutable();

        // Проверяем истечение срока действия или превышение лимита использования
        $isExpired = $closeDate <= $nowDate;
        $isLimitExceeded = $promoCodeType->getLimitUsage() <= $promoCodeType->getCountUsage();

        if ($isLimitExceeded || $isExpired) {
            return 'Промокод больше не действителен!';
        }

        return false;
    }
}