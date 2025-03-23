<?php

namespace common\bitrix24\manager;

use backend\components\AdminHandler;
use common\types\DealBx24;
use frontend\components\Bx24Handler;
use Yii;
use yii\base\InvalidConfigException;

class DealBx24Manager implements DealManagerInterface
{
    private Bx24Handler $bx24Handler;
    const STATUS_NEW = 'NEW'; // Новая заявка
    const STATUS_NEW_EKB = 'C1:NEW'; // Новая заявка
    const STATUS_6_NO_PAY = '6'; // Не оплачено
    const STATUS_EKB_NO_PAY = 'C1:PREPARATION'; // Не оплачено

    public function __construct(
        Bx24Handler $bx24Handler
    ) {
        $this->bx24Handler = $bx24Handler;
    }

    public function findExistingDeals(int $contactId): array
    {
        return $this->bx24Handler->getDeals(
            $contactId,
            ["STAGE_ID" => [self::STATUS_NEW, self::STATUS_NEW_EKB, self::STATUS_6_NO_PAY, self::STATUS_EKB_NO_PAY]],
            ['ID']
        );
    }

    public function createDeal(DealBx24 $deal): int
    {
        $arProperty = $deal->assignValuesForBitrixAttributes($deal->attributes);

        $arUtm = AdminHandler::getUtmProperty();

        if (!empty($arUtm)){
            $arProperty = array_merge($arProperty, $arUtm);
        }

        return $this->bx24Handler->addDeal($arProperty);
    }

    public function updateDeal(DealBx24 $deal): bool
    {
        $arProperty = $deal->assignValuesForBitrixAttributes($deal->attributes);

        return $this->bx24Handler->updateDeal(
            $deal->id,
            $arProperty
        ) > 0;
    }

    /**
     * @throws InvalidConfigException
     */
    public function initDaysWorkFlow(DealBx24 $deal)
    {
        $arProperty = $deal->assignValuesForBitrixAttributes($deal->attributes);

        $dateNowTimeStamp = Yii::$app->formatter->asTimestamp('now + 1 day');
        $dateFormTimeStamp = Yii::$app->formatter->asTimestamp($arProperty['UF_CRM_631ACEA44064C']);

        $queryParams = [
            "TEMPLATE_ID" => 40,
            "DOCUMENT_ID" => ['crm', 'CCrmDocumentDeal', 'DEAL_' . $deal->id],
            "PARAMETERS" => [
                "Vremya_iz_sdelki" => DealBx24::DELIVERY_TIME[$arProperty['UF_CRM_631ACEA41C74C']],
                "Ration_iz_sdelki" => DealBx24::DAYS[$arProperty['UF_CRM_631ACEA429E59']],
                "extraInfo" => false,
                "delivery_date" => $dateNowTimeStamp > $dateFormTimeStamp ? Yii::$app->formatter->asDate($dateNowTimeStamp) : Yii::$app->formatter->asDate($dateFormTimeStamp),
            ]
        ];

        $this->bx24Handler->updateDaysWorkFlowByDeal($queryParams);
    }

    public function syncDealData(DealBx24 $deal): void
    {
        $data = $this->bx24Handler->getDeal($deal->getId());
        $deal->setAttributes($data);
    }
}