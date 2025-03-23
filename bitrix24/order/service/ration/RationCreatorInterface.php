<?php

namespace common\bitrix24\order\service\ration;

use common\types\DealBx24;
use frontend\components\menu\models\{MealTimeReplace, GiftMealTime};

interface RationCreatorInterface {
    public function create(
        DealBx24 $deal,
        ?MealTimeReplace $mealReplace,
        ?GiftMealTime $giftMeal
    ): void;

    public function getRations(): ?array;
}