<?php

namespace common\bitrix24\order\cost;

use backend\models\MealTime;
use yii\helpers\ArrayHelper;

class ReplacedMealTimeCost implements CalculatorInterface
{
    private array $rep_ml_list;
    private ?array $infoByPrices;
    private float $discount = 0;

    /**
     * @param array $rep_ml_list
     * @param ?array $infoByPrices
     */
    public function __construct(array $rep_ml_list, ?array $infoByPrices)
    {
        $this->rep_ml_list = $rep_ml_list;
        $this->infoByPrices = $infoByPrices;
    }

    public function getCost(float $cost): float
    {
        if (isset($this->infoByPrices)) {
            foreach ($this->rep_ml_list as $date => $day) {
                foreach ($day as $meal_time_char_id => $rep_meal_time_char_id) {
                    $this->rep_ml_list[$date][$meal_time_char_id] = $this->infoByPrices[$rep_meal_time_char_id]['char_import_id'];
                    $this->discount += $this->infoByPrices[$rep_meal_time_char_id]['price'] > $this->infoByPrices[$meal_time_char_id]['price']
                        ? $this->infoByPrices[$rep_meal_time_char_id]['price'] - $this->infoByPrices[$meal_time_char_id]['price']
                        : 0;
                }
            }
        }

        $cost += $this->discount;

        return $cost;
    }

    public function getDiscountValue()
    {
        return $this->discount;
    }

    public function getName(): string
    {
        return 'replaced_ml';
    }
}