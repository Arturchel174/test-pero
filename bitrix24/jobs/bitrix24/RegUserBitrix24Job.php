<?php

namespace common\jobs\bitrix24;

use common\bitrix24\user\helper\UserBitrixHelper;
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

class RegUserBitrix24Job extends BaseObject implements JobInterface
{
    public $user_id;
    public $username;

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

            $userInfo = UserInfo::find()->where(['user_id' => $this->user_id])->one() ?? new UserInfo();
            $userInfo->user_id = $this->user_id;
            $userInfo->phone = $this->username;
            $userInfo->first_name = $userBitrixHelper->getUserName();
            $userInfo->bx_id = $userBitrixHelper->getBxId();
            $userInfo->balance = $userBitrixHelper->getBalanceBonus();
            $userInfo->deposit = $userBitrixHelper->getBalanceDeposit();

            $user = User::findOne($this->user_id);
            $user->secure_status = $userBitrixHelper->getSecureStatus();

            if (!$userInfo->save()) {
                throw new ServerErrorHttpException(implode ( "\n" , \yii\helpers\ArrayHelper::getColumn ( $userInfo->errors , 0 , false ) ));
            }

            if (!$user->save()) {
                throw new ServerErrorHttpException(implode ( "\n" , \yii\helpers\ArrayHelper::getColumn ( $user->errors , 0 , false ) ));
            }

        } catch (\Exception $e) {
            // Логируем ошибку
            Yii::error("Ошибка отправки запроса: {$e->getMessage()}");
            // Перебрасываем исключение, чтобы задача была повторена
            throw $e;
        }
    }

    public function getTtr()
    {
        return 30;
    }

    public function canRetry($attempt, $error)
    {
        // TODO: Implement canRetry() method.
    }
}