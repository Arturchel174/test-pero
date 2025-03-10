<?php

namespace common\bitrix24\order\type;

use akhur0286\paykeeper\models\PaykeeperInvoice;
use yii\db\StaleObjectException;

class PaykeeperType
{
    private string $clientid;
    private string $email;
    private string $service_name;
    private string $phone;
    private float $cost;
    private int $related_id;
    private array $paymentData;
    private $paykeeper;

    /**
     * @param string $clientid
     * @param string $email
     * @param string $service_name
     * @param string $phone
     * @param float $cost
     * @param int $related_id
     */

    /**
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function __construct(string $clientid, string $email, string $service_name, string $phone, float $cost, int $related_id, $paykeeper)
    {
        $this->clientid = $clientid;
        $this->email = $email;
        $this->service_name = $service_name;
        $this->phone = $phone;
        $this->cost = $cost;
        $this->related_id = $related_id;
        $this->paykeeper = $paykeeper;

        $this->paymentData = [
            "clientid" => $this->clientid,
            "email" => $this->email,
            "service_name" => $this->service_name,
            "phone" => $this->phone
        ];

        $this->delete();
    }

    public function create(): string
    {
        return $this->paykeeper->create($this->related_id, $this->cost, $this->paymentData);
    }

    /**
     * @throws \Throwable
     * @throws StaleObjectException
     */
    private function delete()
    {
        $model = PaykeeperInvoice::findOne(['related_id' => $this->related_id]);

        if(isset($model) && $model->delete() === false){
            throw new \RuntimeException('Не удалось получить ссылку на оплату!');
        }
    }
}