<?php

namespace common\bitrix24\order\service\discount\factory;

use common\bitrix24\order\cost\BonusesCost;
use common\bitrix24\order\cost\DepositCost;
use common\bitrix24\order\cost\PromoCodeCost;
use common\bitrix24\order\cost\ReplacedMealTimeCost;
use common\bitrix24\order\service\discount\context\DiscountContext;

class DefaultDiscountStrategyFactory implements DiscountStrategyFactoryInterface
{
    public function createStrategies(DiscountContext $context): array {
        $strategies = [];

        if ($this->shouldApplyMealTimeDiscount($context)) {
            $strategies[] = new ReplacedMealTimeCost(
                $context->mealReplace->rep_ml_list,
                $context->mealReplace->getInfoByPrices()
            );
        }

        if ($this->shouldApplyPromoCode($context)) {
            $strategies[] = new PromoCodeCost(
                $context->promoCode,
                $context->program->getCountDay(),
                $context->program->getProgramId()
            );
        } elseif($this->shouldApplyBonusesOrDeposit($context)) {
            $strategies[] = new DepositCost($context->user->getDeposit());
            $strategies[] = new BonusesCost($context->user->getBalance());
        }

        return $strategies;
    }

    private function shouldApplyMealTimeDiscount(DiscountContext $context): bool
    {
        return $context->mealReplace !== null
            && !empty($context->mealReplace->rep_ml_list)
            && $context->mealReplace->getInfoByPrices() !== null;
    }

    private function shouldApplyPromoCode(DiscountContext $context): bool
    {
        return $context->promoCode !== null
            && $context->promoCode->isFlagValidate();
    }

    private function shouldApplyBonusesOrDeposit(DiscountContext $context): bool
    {
        return $context->useBonusesOrDeposit;
    }
}