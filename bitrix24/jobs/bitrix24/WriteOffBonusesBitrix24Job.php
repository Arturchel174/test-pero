<?php

namespace common\jobs\bitrix24;

use common\bitrix24\user\helper\UserBitrixHelper;
use common\handlers\CrmItemRequest;
use common\models\User;
use frontend\components\Bx24Handler;
use frontend\models\UserInfo;
use Yii;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;
use yii\queue\RetryableJobInterface;
use yii\web\ServerErrorHttpException;

class WriteOffBonusesBitrix24Job extends BaseObject implements JobInterface
{
    public $deal_id;
    public $username;
    public $bonuses;
    public $user_id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     * /
     * @throws Exception
     * @throws ServerErrorHttpException
     * /**
     */
    public function execute($queue)
    {

        try {
            $userBitrixHelper = new UserBitrixHelper(new Bx24Handler('+'.$this->username));

            if (!$userBitrixHelper->registerUserBx24()) {
                throw new ServerErrorHttpException('Не удалось получить данные для пользователя из б24.');
            }

            $balance = $userBitrixHelper->getBalanceBonus();

            if($balance >= $this->bonuses){
                // Создание объекта запроса
                $request = (new CrmItemRequest(175));

                $request
                    ->setContactIds([$userBitrixHelper->getBxId()])
                    ->setCustomField('ufCrm4_1729069772082', $this->bonuses)         // CyMMA (предположительно сумма)
                    ->setCustomField('ufCrm4_1729069794381', 1196)            // Тип: Списание
                    ->setCustomField('ufCrm4_1729069921799', 1201)            // Источник: Клиент (сайт)
                    ->setCustomField('ufCrm4_1730196297', 1254)               // Статус: Действует
                    ->setCustomField('ufCrm4_1730896018', "Списание бонусов за сделку $this->deal_id")
                    ->setParentId2('СДЕЛКА');

                if (!$userBitrixHelper->bx24Handler::updateBonuses($request->toArray())){
                    throw new ServerErrorHttpException('Не удалось списать бонусы!');
                }

                $balance -= $this->bonuses;
            }

            $userInfo = UserInfo::findOne(['user_id' => $this->user_id]);

            if(isset($userInfo)){
                $userInfo->balance = $balance;
                $userInfo->save(false);
            }

        } catch (\Exception $e) {
            // Логируем ошибку
            Yii::error("Ошибка отправки запроса: {$e->getMessage()}");
            // Перебрасываем исключение, чтобы задача была повторена
            throw $e;
        }
    }
}