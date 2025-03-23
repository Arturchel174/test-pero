<?php

namespace common\bitrix24\order\service\discount\context;

use common\bitrix24\exception\InvalidOrderCostException;
use common\bitrix24\order\type\ProgramType;
use common\bitrix24\order\type\PromoCodeType;
use common\bitrix24\order\type\UserType;
use frontend\components\menu\models\MealTimeReplace;

class DiscountContext {
    public ProgramType $program;
    public UserType $user;
    public ?MealTimeReplace $mealReplace;
    public ?PromoCodeType $promoCode;
    public bool $useBonusesOrDeposit;

    public function __construct(
        ProgramType $program,
        UserType $user,
        ?MealTimeReplace $mealReplace,
        ?PromoCodeType $promoCode,
        bool $useBonusesOrDeposit = false
    ) {
        $this->promoCode = $promoCode;
        $this->mealReplace = $mealReplace;
        $this->user = $user;
        $this->program = $program;
        $this->useBonusesOrDeposit = $useBonusesOrDeposit;

        if ($this->program->getPrice() <= 0) {
            throw new InvalidOrderCostException($this->program->getPrice());
        }

        $this->mealReplace->setInfoByPrices();
    }
}