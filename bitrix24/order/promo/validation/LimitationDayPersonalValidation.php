<?php

namespace common\bitrix24\order\promo\validation;

use common\bitrix24\order\type\PromoCodeType;
use DateTimeImmutable;

class LimitationDayPersonalValidation extends MinePromoCodeValidation implements ValidationInterface
{
    private int $count_days;

    public function __construct($user_id, int $count_days)
    {
        $this->count_days = $count_days;
        parent::__construct($user_id);
    }

    /**
     * @throws \Exception
     */
    public function checkError(PromoCodeType $promoCodeType)
    {
        if (static::isPersonalTypePromoCode($promoCodeType->getTypeName())) {
            // Создаем immutable объекты дат
            $promoDate = new DateTimeImmutable($promoCodeType->getCreateDate());
            $nowDate = new DateTimeImmutable();

            // Вычисляем разницу между датами
            $interval = $promoDate->diff($nowDate);

            // Проверяем превышение лимита дней
            if ($interval->days >= $this->count_days) {
                return "Срок персонального промокода {$this->count_days} дней!";
            }
        }

        return false;
    }
}