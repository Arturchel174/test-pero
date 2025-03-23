<?php

namespace common\bitrix24\order\service\ration;

use common\types\DealBx24;
use frontend\components\menu\models\GiftMealTime;
use frontend\components\menu\models\MealTimeReplace;
use JsonException;

class RationCreatorService implements RationCreatorInterface
{
    private ?array $rations;
    /**
     * @throws \InvalidArgumentException
     * @throws JsonException|\Throwable
     */
    public function create(
        DealBx24 $deal,
        ?MealTimeReplace $mealReplace,
        ?GiftMealTime $giftMeal
    ): void {
        if ($mealReplace === null) {
            return;
        }

        $this->rations = $mealReplace->getRations(
            $deal->count_days,
            $deal->program_id,
            $deal->menu_id,
            $deal->delivery_date
        );

        if ($this->rations !== null && $giftMeal !== null) {
            $giftMeal->mergeDateGiftWithRations($this->rations);
        }

        $deal->setRations(json_encode($this->rations, JSON_THROW_ON_ERROR));
    }

    public function getRations(): ?array
    {
        return $this->rations;
    }
}