<?php

namespace common\bitrix24\order\storage;

use common\bitrix24\order\type\ProgramType;
use common\bitrix24\order\type\PromoCodeType;
use common\bitrix24\order\type\UserType;
use common\handlers\DiscountHelper;
use common\types\DealBx24;

class PostDealStorage implements StorageInterface
{
    private DealBx24 $dealBx24;
    private ProgramType $programType;
    private UserType $userType;
    private ?PromoCodeType $promoCodeType;

    /**
     * @param DealBx24 $dealBx24
     */
    public function __construct(DealBx24 $dealBx24, ProgramType $programType, UserType $userType, PromoCodeType $promoCodeType = null)
    {
        $this->dealBx24 = $dealBx24;
        $this->programType = $programType;
        $this->userType = $userType;
        $this->promoCodeType = $promoCodeType;
    }

    public function load(): array
    {
        $this->dealBx24->program_name = "{$this->programType->getCalories()} ккал {$this->programType->getProgramName()}";
        $this->dealBx24->username = $this->userType->getPhoneWithMask();
        $this->dealBx24->currency = $this->programType::CURRENCY_ID;
        $this->dealBx24->contact_id = $this->userType->getBxId() ?? null;
        $this->dealBx24->count_days = array_flip($this->dealBx24::DAYS)[$this->dealBx24->count_days];
        $this->dealBx24->delivery_time = array_flip($this->dealBx24::DELIVERY_TIME)[$this->dealBx24->delivery_time];
        $this->dealBx24->preferred_time = $this->dealBx24->count_days / 2;
        $this->dealBx24->source_id = $this->dealBx24::SOURCE_SITE_ID;
        $this->dealBx24->assigned_by_id = $this->dealBx24::ASSIGNED_BY_ADMIN_ID;

        if ($this->promoCodeType !== null){
            $this->dealBx24->promo_code = $this->promoCodeType->getPromoCodeName();

            if(DiscountHelper::isTypeNumberOrPercent($this->promoCodeType->getTypeDiscount())){
                $this->dealBx24->promocode_sale = $this->promoCodeType->getValueDiscount();
            }

            if(DiscountHelper::isTypeDay($this->promoCodeType->getTypeDiscount())){
                $this->dealBx24->promocode_days = $this->promoCodeType->getValueDiscount();
            }

            if(DiscountHelper::isTypeString($this->promoCodeType->getTypeDiscount())){
                $this->dealBx24->promocode_string = $this->promoCodeType->getValueDiscount();
            }
        }

        return $this->dealBx24->attributes;
    }

    public function save($item)
    {
        // TODO: Implement save() method.
    }
}