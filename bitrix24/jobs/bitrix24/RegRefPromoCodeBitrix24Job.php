<?php

namespace common\jobs\bitrix24;

use common\bitrix24\user\helper\UserBitrixHelper;
use common\models\Promo;
use common\models\User;
use common\types\PromoLists;
use frontend\components\Bx24Handler;
use frontend\models\UserInfo;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class RegRefPromoCodeBitrix24Job  extends BaseObject implements JobInterface
{
    public $user_id;
    public $promo_code_id;

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function execute($queue)
    {
        try {
            $promo = new Promo();
            $promoLists = $promo->createPromoList($this->promo_code_id);

            if(is_null($promoLists->getValuePromoCode())){
                throw new ServerErrorHttpException('Не удалось получить данные по промокоду.');
            }

            $userInfo = UserInfo::findOne(['user_id' => $this->user_id]);

            if(is_null($userInfo->bx_id)){
                throw new ServerErrorHttpException('Такого пользователя не существует.');
            }

            $userBitrixHelper = new UserBitrixHelper(new Bx24Handler());

            $userBitrixHelper->setBxId($userInfo->bx_id);

            $userBitrixHelper->sendRefPromoCode($promoLists->getPropertyBx24());
            $userBitrixHelper->sendPromoCodeInContact($promoLists->getPromoCode());

        } catch (\Exception $e) {
            // Логируем ошибку
            Yii::error("Ошибка отправки запроса: {$e->getMessage()}");
            // Перебрасываем исключение, чтобы задача была повторена
            throw $e;
        }
    }
}