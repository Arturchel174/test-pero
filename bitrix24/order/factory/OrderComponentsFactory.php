<?php

namespace common\bitrix24\order\factory;

use common\bitrix24\manager\DealBx24Manager;
use common\bitrix24\order\service\CreateOrderService;
use common\bitrix24\order\service\deal\DealTypeCreatorService;
use common\bitrix24\order\service\discount\DiscountCalculatorService;
use common\bitrix24\order\service\discount\factory\DefaultDiscountStrategyFactory;
use common\bitrix24\order\service\ration\RationCreatorService;
use common\bitrix24\order\storage\YiiProgramStorage;
use common\bitrix24\order\storage\YiiPromoCodeStorage;
use common\bitrix24\order\storage\YiiUserStorage;
use common\bitrix24\order\type\ProgramType;
use common\bitrix24\order\type\PromoCodeType;
use common\bitrix24\order\type\RegionType;
use common\bitrix24\order\type\UserType;
use common\types\DealBx24;
use frontend\components\Bx24Handler;
use frontend\components\menu\models\GiftMealTime;
use frontend\components\menu\models\MealTimeReplace;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class OrderComponentsFactory
{
    private Request $request;
    private array $sessionRegions;
    private DealBx24 $deal;
    private ?MealTimeReplace $mealTimeReplace = null;
    private ?GiftMealTime $giftMealTime = null;

    public function __construct(
        Request $request,
        array $sessionRegions
    ) {
        $this->sessionRegions = $sessionRegions;
        $this->request = $request;
    }

    public function createValidatedComponents(): array
    {
        $this->createDealBx24();
        $this->createMealTimeReplace();
        $this->createGiftMealTime();

        $programType = new ProgramType(new YiiProgramStorage(
            $this->deal->program_id,
            $this->deal->count_days
        ));

        $userType = new UserType(new YiiUserStorage(
            $this->deal->username,
            $this->deal->isGuest
        ));

        $promoCodeType = null;
        if ($userType->isNotGuest() && !empty($this->deal->promo_code)) {
            $promoCodeType = new PromoCodeType(
                new YiiPromoCodeStorage($this->deal->promo_code)
            );
        }

        $createOrderService = new CreateOrderService($userType);

        if ($promoCodeType !== null) {
            $createOrderService->setPromoCodeType($promoCodeType);
        }

        return [
            'deal' => $this->deal,
            'region' => $this->createRegionType(),
            'mealReplace' => $this->mealTimeReplace,
            'giftMeal' => $this->giftMealTime,
            'discountCalculator' => new DiscountCalculatorService(new DefaultDiscountStrategyFactory()),
            'programType' => $programType,
            'userType' => $userType,
            'promoCodeType' => $promoCodeType,
            'createOrderService' => $createOrderService,
            'rationCreatorService' => new RationCreatorService(),
            'dealTypeCreator' => new DealTypeCreatorService(),
            'dealBx24Manager' => new DealBx24Manager(new Bx24Handler()),
            'orderTypeFactory' => new OrderTypeFactory(),
            'paykeeperFactory' => new PaykeeperTypeFactory(Yii::$app),
        ];
    }

    private function createDealBx24()
    {
        $deal = new DealBx24(['scenario' => DealBx24::ORDER_PICKING]);
        $this->loadAndValidate($deal, 'DealBx24');
        $this->deal = $deal;
    }

    private function createMealTimeReplace()
    {
        $mealReplace = new MealTimeReplace();
        if ($this->loadAndValidate($mealReplace, 'MealTimeReplace', false)) {
            $this->mealTimeReplace = $mealReplace;
        }
    }

    private function createGiftMealTime()
    {
        $giftMeal = new GiftMealTime();
        if ($this->loadAndValidate($giftMeal, 'GiftMealTime', false, ['gift_ml_ids'])) {
            $this->giftMealTime = $giftMeal;
        }
    }

    private function createRegionType(): RegionType
    {
        return new RegionType(
            $this->sessionRegions['currentRegionId'] ?? 0,
                $this->sessionRegions['status_category'] ?? '',
                $this->sessionRegions['deal_status_new'] ?? '',
                $this->sessionRegions['deal_city_id'] ?? 0
        );
    }

    private function loadAndValidate(
        $model,
        string $formName,
        bool $required = true,
        array $attributes = null
    ): bool {
        if (!$model->load($this->request->post(), $formName)) {
            if ($required) {
                throw new InvalidArgumentException("Failed to load {$formName} data");
            }
            return false;
        }

        if (!$model->validate($attributes)) {
            if ($required) {
                $errors = implode(', ', ArrayHelper::getColumn($model->errors, 0));
                throw new InvalidArgumentException("Validation failed for {$formName}: {$errors}");
            }
            return false;
        }

        return true;
    }

}