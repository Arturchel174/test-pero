<?php

namespace common\jobs\bitrix24;

use common\bitrix24\user\helper\UserBitrixHelper;
use Exception;
use frontend\components\Bx24Handler;
use frontend\models\UserInfo;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;
use yii\web\ServerErrorHttpException;

class WriteOffDepositBitrix24Job  extends BaseObject implements JobInterface
{
    public $deal_id;
    public $username;
    public $deposit;
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

            $balance = $userBitrixHelper->getBalanceDeposit();

            if($balance >= $this->deposit){
                $balance -= $this->deposit;
            }

            if (!$userBitrixHelper->bx24Handler->updateDepositByContact($balance)){
                throw new ServerErrorHttpException('Не удалось списать бонусы!');
            }

            $userInfo = UserInfo::findOne(['user_id' => $this->user_id]);

            if(isset($userInfo)){
                $userInfo->deposit = $balance;
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