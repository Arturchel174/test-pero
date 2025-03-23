<?php

namespace common\bitrix24\order\factory;

use common\bitrix24\order\type\PaykeeperType;
use common\bitrix24\order\type\DealType;
use common\bitrix24\order\type\OrderType;
use common\bitrix24\order\type\UserType;
use yii\db\StaleObjectException;
use yii\web\Application;

class PaykeeperTypeFactory
{
    private Application $app;

    public function __construct(
        Application $app
    ) {
        $this->app = $app;
    }

    /**
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function create(
        ?OrderType $orderType,
        ?DealType $dealType,
        UserType $userType
    ): PaykeeperType {
        return new PaykeeperType(
            $userType->getFirstName(),
            "s.fdsf01@mail.ru",
            $dealType !== null ? $dealType->getProgramName() : '',
            $dealType !== null ? $dealType->getUsername() : '',
            $dealType !== null ? $dealType->getOpportunity() : 0.0,
            $orderType->getDealId(),
            $this->app->paykeeper
        );
    }
}