<?php

namespace common\bitrix24\order\factory;

use common\bitrix24\exception\DealCreationException;
use common\bitrix24\exception\DealUpdationException;
use common\bitrix24\exception\OrderValidationException;
use common\bitrix24\manager\DealBx24Manager;
use common\bitrix24\manager\DealManagerInterface;
use common\bitrix24\order\service\CreateOrderService;
use common\bitrix24\order\service\deal\DealTypeCreatorInterface;
use common\bitrix24\order\service\discount\context\DiscountContext;
use common\bitrix24\order\service\discount\DiscountCalculatorServiceInterface;
use common\bitrix24\order\service\ration\RationCreatorInterface;
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
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;

/**
 * Фабрика для создания заказов в системе Bitrix24
 *
 * @ method int addDealBx24() Создает новую сделку
 * @ method void updateDealBx24() Обновляет данные сделки
 */
class OrderFactory
{
    private DealBx24 $deal;
    private ?MealTimeReplace $mealReplace;
    private ?GiftMealTime $giftMeal;
    private CreateOrderService $createOrderService;
    private ProgramType $programType;
    private UserType $userType;
    private ?DealType $dealType = null;
    private ?PromoCodeType $promoCodeType;
    private ?OrderType $orderType = null;
    private ?PaykeeperType $paykeeperType = null;
    private RegionType $regionType;
    private DiscountCalculatorServiceInterface $discountCalculator;
    private RationCreatorInterface $rationCreatorService;
    private DealTypeCreatorInterface $dealTypeCreator;
    private DealManagerInterface $dealManager;
    private OrderTypeFactoryInterface $orderTypeFactory;
    private PaykeeperTypeFactory $paykeeperFactory;


    /**
     * @throws \Throwable
     * @throws \JsonException
     */
    public function __construct(
        DealBx24 $deal,
        RegionType $regionType,
        DiscountCalculatorServiceInterface $discountCalculator,
        ProgramType $programType,
        UserType $userType,
        ?PromoCodeType $promoCodeType,
        CreateOrderService $createOrderService,
        RationCreatorInterface $rationCreatorService,
        DealTypeCreatorInterface $dealTypeCreator,
        DealManagerInterface $dealManager,
        OrderTypeFactoryInterface $orderTypeFactory,
        PaykeeperTypeFactory $paykeeperFactory,
        ?MealTimeReplace $mealReplace = null,
        ?GiftMealTime $giftMeal = null
    ) {
        $this->rationCreatorService = $rationCreatorService;
        $this->deal = $deal;
        $this->regionType = $regionType;
        $this->discountCalculator = $discountCalculator;
        $this->programType = $programType;
        $this->userType = $userType;
        $this->promoCodeType = $promoCodeType;
        $this->createOrderService = $createOrderService;
        $this->dealTypeCreator = $dealTypeCreator;
        $this->dealManager = $dealManager;
        $this->orderTypeFactory = $orderTypeFactory;
        $this->paykeeperFactory = $paykeeperFactory;
        $this->mealReplace = $mealReplace;
        $this->giftMeal = $giftMeal;

        $this->initializeComponents();
    }


    /**
     * @throws \Throwable
     * @throws \JsonException
     */
    private function initializeComponents(): void
    {
        $this->createRations();
        $this->validationPromoCode();
        $this->discountCalculation();
        $this->createDealType();
        $this->collectingDealBx24();
    }

    public function getDealHash(): string
    {
        $array = $this->deal->assignValuesForBitrixAttributes($this->deal->attributes);
        unset($array['ID']);
        $string = serialize($array);
        return md5($string);
    }

    public function getRations(): ?array
    {
        return $this->rationCreatorService->getRations();
    }

    public function getInfoByPrices(): ?array
    {
        return $this->mealReplace->getInfoByPrices();
    }

    public function getDeal(): DealBx24
    {
        return $this->deal;
    }

    public function addDealBx24($onDaysWorkFlow = true): int
    {
        $this->ensureDealExists();

        if ($this->deal->id <= 0) {
            throw new DealCreationException('Не удалось создать сделку!');
        }

        $this->initDaysWorkFlow($onDaysWorkFlow);

        return $this->deal->id;
    }

    public function updateDealBx24($onDaysWorkFlow = true): bool
    {
        if (!$this->dealType->getId() && $this->addDealBx24($onDaysWorkFlow) > 0) {
            return true;
        }

        if ($this->dealManager->updateDeal($this->deal) !== true) {
            throw new DealUpdationException('Не удалось обновить сделку!');
        }

        $this->initDaysWorkFlow($onDaysWorkFlow);

        return true;
    }

    /**
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public function createOrder(): void
    {
        if ($this->deal->id <= 0) {
            throw new DealUpdationException('Не удалось создать заказ!');
        }

        $this->createOrderType();
        $this->saveOrder();
        $this->createPaykeeperType();
    }
    private function initDaysWorkFlow($onDaysWorkFlow = true): void
    {
        if($onDaysWorkFlow && $this->deal->id > 0){
            try {
                $this->dealManager->initDaysWorkFlow($this->deal);
            }catch (InvalidConfigException $e){
                throw new DealUpdationException('Не удалось настроить график доставки!');
            }
        }
    }
    private function ensureDealExists(): void {
        if (!$this->dealType->getId()) {
            $this->deal->id = $this->dealManager->createDeal($this->deal);
        }
    }

    public function getUrlPaykeeper(): ?string
    {
        return $this->paykeeperType !== null
            ? $this->paykeeperType->create()
            : null;
    }

    private function createDealType(): void
    {
        $this->dealType = $this->dealTypeCreator->create(
            $this->deal,
            $this->programType,
            $this->userType,
            $this->promoCodeType
        );
    }

    /**
     * @throws \Throwable
     * @throws \JsonException
     */
    private function createRations(): void
    {
        $this->rationCreatorService->create($this->deal, $this->mealReplace, $this->giftMeal);
    }

    private function validationPromoCode(): void
    {
        $this->createOrderService->validationPromoCode();
    }

    private function discountCalculation(): void
    {
        $this->deal->opportunity = $this->discountCalculator->calculate(
            new DiscountContext(
                $this->programType,
                $this->userType,
                $this->mealReplace,
                $this->promoCodeType,
                $this->deal->bonus
            )
        );
    }

    private function collectingDealBx24(): void
    {
        if ($this->dealType === null) {
            throw new \RuntimeException('DealType not initialized');
        }

        $contactId = $this->dealType->getContactId();

        if ($contactId !== null) {
            $existingDeals = $this->dealManager->findExistingDeals($contactId);
        }

        if (!empty($existingDeals)) {
            $this->dealType->setId(end($existingDeals)['ID']);
        }

        $this->dealTypeCreator->configure(
            $this->dealType,
            $this->regionType,
            $this->userType
        );

        $this->deal->setAttributes($this->dealType->getProperty());
    }

    private function createOrderType(): void
    {
        $this->orderType = $this->orderTypeFactory->create(
            $this->deal,
            $this->userType,
            $this->regionType,
            $this->discountCalculator->getDiscountValues()
        );
    }

    private function saveOrder(): void
    {
        if ($this->orderType !== null) {
            $this->orderType->save();
        } else {
            throw new OrderValidationException('Нет данных для сохранения сделки!');
        }
    }

    /**
     * @throws StaleObjectException
     * @throws \Throwable
     */
    private function createPaykeeperType(): void
    {
        $this->paykeeperType = $this->paykeeperFactory->create(
            $this->orderType,
            $this->dealType,
            $this->userType
        );
    }
}