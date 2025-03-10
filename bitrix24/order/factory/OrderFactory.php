<?php

namespace common\bitrix24\order\factory;

use common\bitrix24\order\cost\BonusCost;
use common\bitrix24\order\cost\PromoCodeCost;
use common\bitrix24\order\cost\ReplacedMealTimeCost;
use common\bitrix24\order\service\CreateOrderService;
use common\bitrix24\order\storage\DealOrderStorage;
use common\bitrix24\order\storage\PostDealStorage;
use common\bitrix24\order\storage\YiiProgramStorage;
use common\bitrix24\order\storage\YiiPromoCodeStorage;
use common\bitrix24\order\storage\YiiUserStorage;
use common\bitrix24\order\type\DealType;
use common\bitrix24\order\type\OrderType;
use common\bitrix24\order\type\PaykeeperType;
use common\bitrix24\order\type\ProgramType;
use common\bitrix24\order\type\PromoCodeType;
use common\bitrix24\order\type\RegionType;
use common\bitrix24\order\type\UserType;
use common\types\DealBx24;
use frontend\components\menu\models\GiftMealTime;
use frontend\components\menu\models\MealTimeReplace;
use Yii;
use yii\db\StaleObjectException;

class OrderFactory
{
    private $post;
    private ?DealBx24 $dealBx24 = null;
    private ?MealTimeReplace $mealTimeReplace = null;
    private ?GiftMealTime $giftMealTime = null;
    private CreateOrderService $createOrderService;
    private ProgramType $programType;
    private UserType $userType;
    private DealType $dealType;
    private ?PromoCodeType $promoCodeType;
    private ?OrderType $orderType;
    private ?PaykeeperType $paykeeperType;
    private ?array $rations;
    private RegionType $regionType;
    /**
     * @var mixed
     */
    private array $region;

    /**
     * @param $post
     */
    public function __construct($post, array $region)
    {
        $this->post = $post;
        $this->region = $region;
        $this->createRegionType();
        $this->createProgramType();
        $this->createUserType();
        $this->createOrderService();
        $this->createRations();
        $this->validationPromoCode();
        $this->discountCalculation();
        $this->createDealType();
    }

    public function addDealBx24(): int
    {
        $this->collectingDealBx24();

        $this->dealBx24->id = $this->dealBx24->addDeal();

        if($this->dealBx24->id > 0){
            return $this->dealBx24->id;
//            $this->createOrderType();
//            $this->saveOrder();
//            $this->createPaykeeperType();
        }else{
            throw new \RuntimeException('Не удалось создать сделку!');
        }

    }

    /**
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function updateDealBx24()
    {
        $this->collectingDealBx24();

        if ($this->dealBx24->id > 0) {
            $this->createOrderType();
            $this->saveOrder();
            $this->createPaykeeperType();
        } else {
            throw new \RuntimeException('Не удалось обновить сделку!');
        }
    }

    public function validationPromoCode()
    {
        $this->createOrderService->validationPromoCode();
    }

    public function discountCalculation()
    {
        if(isset($this->createOrderService, $this->mealTimeReplace->rep_ml_list)){
            $this->mealTimeReplace->setInfoByPrices();
            $this->createOrderService->discountCalculation(
                new ReplacedMealTimeCost($this->mealTimeReplace->rep_ml_list, $this->mealTimeReplace->getInfoByPrices())
            );
        }

        // Если есть валидный промокод - используем только его
        if ($this->promoCodeType !== null && $this->promoCodeType->isFlagValidate()) {
            $this->createOrderService->discountCalculation(
                new PromoCodeCost(
                    $this->promoCodeType,
                    $this->programType->getCountDay(),
                    $this->programType->getProgramId()
                )
            );
        }
        // Если промокода нет - используем бонусы
        else {
            //получить бонус $this->dealBx24->bonus
            //проверить в б24
            $this->createOrderService->discountCalculation(
                new BonusCost($this->userType->getBalance())
            );
        }
    }

    public function getUserName()
    {
        return $this->dealBx24->username;
    }

    public function getCost(): float
    {
        return $this->createOrderService->getCost();
    }

    public function getRations(): ?array
    {
        return $this->rations;
    }

    public function getUrlPaykeeper(): ?string
    {
        if($this->paykeeperType !== null){
            return $this->paykeeperType->create();
        }

        return null;
    }

    private function createProgramType()
    {
        $this->initModels();

        $this->programType = new ProgramType(
            new YiiProgramStorage($this->dealBx24->program_id, $this->dealBx24->count_days)
        );
    }

    private function createUserType()
    {
        $this->initModels();

        $this->userType = new UserType(
            new YiiUserStorage($this->dealBx24->username)
        );
    }
    private function createPromoCodeType()
    {
        $this->initModels();

        $this->promoCodeType = !empty($this->dealBx24->promo_code)
            ? new PromoCodeType(new YiiPromoCodeStorage($this->dealBx24->promo_code))
            : null;
    }

    private function createOrderService()
    {
        if(isset($this->programType, $this->userType)){
            $this->createOrderService = new CreateOrderService($this->programType, $this->userType);

            if($this->userType->isNotGuest()){
                $this->createPromoCodeType();
            }

            if($this->promoCodeType !== null) {
                $this->createOrderService->setPromoCodeType($this->promoCodeType);
            }
        }
    }

    private function createDealType()
    {
        if(isset($this->programType, $this->userType)){
            $this->dealType = new DealType(
                new PostDealStorage($this->dealBx24, $this->programType, $this->userType, $this->promoCodeType)
            );
        }
    }

    private function loadModel($model, $formName = null, $attributeNames = null, $clearErrors = true)
    {
        if (!$model->load($this->post, $formName)) {
            return null;
        }

        if (!$model->validate($attributeNames)) {
            throw new \RuntimeException(
                implode(', ', \yii\helpers\ArrayHelper::getColumn($model->errors, 0))
            );
        }

        return $model;
    }

    private function initModels()
    {
        if($this->dealBx24 === null){
            $this->dealBx24 = $this->loadModel(
                new DealBx24(['scenario' => DealBx24::ORDER_PICKING]),
                'DealBx24'
            );
        }

        if($this->mealTimeReplace === null){
            $this->mealTimeReplace = $this->loadModel(
                new MealTimeReplace(),
                'MealTimeReplace'
            );
        }

        if($this->giftMealTime === null){
            $this->giftMealTime = $this->loadModel(
                new GiftMealTime(),
                'GiftMealTime',
                'gift_ml_ids'
            );
        }
    }

    private function createRegionType()
    {
        $this->regionType = new RegionType(
            $this->region['currentRegionId'],
            $this->region['status_category'],
            $this->region['deal_status_new'],
            $this->region['deal_city_id']
        );
    }

    private function createRations()
    {
        $this->initModels();

        try {
            $this->rations = $this->mealTimeReplace->getRations(
                $this->dealBx24->count_days,
                $this->dealBx24->program_id,
                $this->dealBx24->menu_id,
                $this->dealBx24->delivery_date
            );
            if (isset($this->rations)) {
                $this->mergeDateGiftWithRations();
                $this->dealBx24->setRations(json_encode($this->rations, JSON_THROW_ON_ERROR));
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage());

        }
    }

    /**
     * @throws \JsonException
     */
    private function mergeDateGiftWithRations()
    {
        if(isset($this->giftMealTime)){
            $this->giftMealTime->mergeDateGiftWithRations($this->rations);
        }
    }

    private function createOrderType()
    {
        $this->orderType = new OrderType(
            new DealOrderStorage($this->dealBx24, $this->userType->getUserId(), $this->regionType->getCurrentRegionId())
        );
    }

    private function saveOrder()
    {
        if($this->orderType !== null){
            $this->orderType->save();
        }
    }

    /**
     * @throws \Throwable
     * @throws StaleObjectException
     */
    private function createPaykeeperType()
    {
        $this->paykeeperType = new PaykeeperType(
            $this->userType->getUsername(),
            "s.fdsf01@mail.ru",
            $this->dealType->getProgramName(),
            $this->dealType->getUsername(),
            $this->dealType->getOpportunity(),
            $this->orderType->getDealId(),
            Yii::$app->paykeeper
        );
    }

    private function collectingDealBx24()
    {
        if(isset($this->dealType)){
            $this->dealType->createTitle();
            $this->dealType->setOpportunity($this->getCost());
            $this->dealType->setCategoryId($this->regionType->getStatusCategory() ?? $this->dealBx24::STATUS_CATEGORY_CHEL);
            $this->dealType->setStageId($this->regionType->getDealStatusNew() ?? $this->dealBx24::STATUS_NEW);
            $this->dealType->setCityId($this->regionType->getDealCityId() ?? 730);
            $this->dealType->setPayInfo($this->dealBx24::ORDER_NO_PAID_VALUE);
        }

        if($this->userType->isNewUser()){
            $this->dealType->addTextForTitle(' — Первый заказ');
        }

        $this->dealBx24->setAttributes($this->dealType->getProperty());
    }

}