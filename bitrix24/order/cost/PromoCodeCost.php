<?php

namespace common\bitrix24\order\cost;

use common\bitrix24\order\type\PromoCodeType;
use common\handlers\DiscountHelper;

class PromoCodeCost implements CalculatorInterface
{
    private PromoCodeType $promoCodeType;
    private int $count_day;
    private int $program_id;
    private float $discount = 0;

    /**
     * @param PromoCodeType $promoCodeType
     * @param int $count_day
     * @param int $program_id
     */
    public function __construct(PromoCodeType $promoCodeType, int $count_day, int $program_id)
    {
        $this->promoCodeType = $promoCodeType;
        $this->count_day = $count_day;
        $this->program_id = $program_id;
    }

    public function getCost(float $cost): float
    {
        if(!$this->promoCodeType->isExitPromoCode()){
            return $cost;
        }

        $fieldValues = $this->promoCodeType->getSalePromoCodeFieldValues($this->count_day);

        if (!empty($fieldValues)) {
            if (count($fieldValues) <= 1) {
                $valueFiled = $fieldValues[0]["value"];
                $typeFiled = $fieldValues[0]["type_sale_value"];
            } else {
                foreach ($fieldValues as $fieldValue) {
                    if (isset($fieldValue["day"]) && $this->isListPromoCodePrograms($fieldValue)) {
                        $valueFiled = $fieldValue["value"];
                        $typeFiled = $fieldValue["type_sale_value"];
                        break;
                    }
                }
            }
        }

        if (isset($valueFiled, $typeFiled)) {
            $discount = new DiscountHelper($valueFiled, $typeFiled, $cost);
            $this->promoCodeType->setValueDiscount($discount->getValueDiscount());
            $this->promoCodeType->setTypeDiscount($discount->getTypeDiscount());
        }

        if (DiscountHelper::isTypeNumberOrPercent($this->promoCodeType->getTypeDiscount())) {
            $this->discount = $this->promoCodeType->getValueDiscount() ?? 0;
        }

        $cost -= $this->discount;

        return $cost;
    }

    public function isListPromoCodePrograms($arPromoResult): bool
    {
        return $arPromoResult["program_id"] === $this->program_id && $arPromoResult["count"] === $this->count_day;
    }

    public function getDiscountValue(): int
    {
        return $this->discount;
    }

    public function getName(): string
    {
        return 'promo_code';
    }
}