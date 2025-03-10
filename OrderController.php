<?php

namespace frontend\controllers;

use common\bitrix24\order\factory\OrderFactory;
use common\bitrix24\response\service\ResponseService;
use frontend\components\AreaDeliveryHandler;
use frontend\components\BeforeOrderHandler;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class OrderController extends Controller
{
    public function actionCreateOrder()
    {
        try {
            if (!Yii::$app->request->isAjax) {
                throw new BadRequestHttpException('Только AJAX-запросы');
            }

            if (Yii::$app->user->isGuest) {
                throw new BadRequestHttpException('Только для авторизированных пользователей');
            }

            $order = new OrderFactory(Yii::$app->request->post(), Yii::$app->session->get('regions'));

            $order->updateDealBx24();

            $url = $order->getUrlPaykeeper();

            if (isset($url)) {
//                return Yii::$app->response->redirect($url);
                return $this->asJson([
                    'success' => true,
                    'url' => $url
                ]);
            }

        } catch (\Exception | \Throwable $e) {
//            Yii::error($e);
            return $this->asJson([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return $this->asJson([
            'success' => false,
            'message' => 'Ошибка сервера'
        ]);
    }

    public function actionCreateDealBx24()
    {
        try {
            if (!Yii::$app->request->isAjax) {
                throw new BadRequestHttpException('Только AJAX-запросы');
            }

            if(!Yii::$app->session->has('dealId')){
                $order = new OrderFactory(Yii::$app->request->post(), Yii::$app->session->get('regions'));
                $dealId = $order->addDealBx24();
                Yii::$app->session->set('dealId', $dealId);
            }else{
                $dealId = Yii::$app->session->get('dealId');
            }

            if ($dealId < 1) {
                return $this->asJson([
                    'success' => false,
                    'message' => 'Не удалось создать сделку, попробуйте еще раз!'
                ]);
            }

            $responseService = new ResponseService();

            $responseService->addAction('SHOW_DEAL_ID');
            $responseService->addValueOfData('dealID', $dealId);

            if (Yii::$app->user->isGuest) {
                $responseService->addAction('SHOW_AUTH_MODAL');
                $responseService->addValueOfData('phone', $order->getUserName());
            }

            $responseService->loadActions();

            return $this->asJson($responseService->getResponse());

        } catch (\Exception $e) {
//            Yii::error($e);
            return $this->asJson([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionAddressOrder()
    {
        try {
            if (!Yii::$app->request->isAjax) {
                throw new BadRequestHttpException('Только AJAX-запросы');
            }

            if (Yii::$app->user->isGuest) {
                throw new BadRequestHttpException('Только для авторизированных пользователей');
            }

            if(!isset($_SESSION['regions']['currentRegionId'])){
                throw new BadRequestHttpException('Ошибка в работе с сессией региона');
            }

            $user_id = Yii::$app->user->identity->getId();

            $areaDelivery = new AreaDeliveryHandler($_SESSION['regions']['currentRegionId']);

            $responseService = new ResponseService();

            $order = new BeforeOrderHandler($user_id);
            $order->initAddressesAndDates();
            $order->createAddressAndDatesResponse($responseService, 'RENDER_ADDRESS_MODAL');

            $responseService->addValueOfData('areaDelivery', $areaDelivery->getAreaDelivery());

            $responseService->loadActions();

            return $this->asJson($responseService->getResponse());

        } catch (\Exception $e) {
//            Yii::error($e);
            return $this->asJson([
                'success' => false,
                'message' => 'Ошибка сервера'
            ]);
        }
    }
}