<?php

namespace common\bitrix24\payment;

use yii\web\ServerErrorHttpException;

class PaykeeperGateway implements PaymentGatewayInterface
{
    private string $apiUrl;
    private string $apiToken;

    public function __construct(string $apiUrl, string $apiToken)
    {
        $this->apiUrl = $apiUrl;
        $this->apiToken = $apiToken;
    }

    public function generatePaymentLink(float $amount, array $metadata): string
    {
        $client = new \yii\httpclient\Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($this->apiUrl)
            ->setHeaders(['Authorization' => 'Bearer ' . $this->apiToken])
            ->setData([
                'amount' => $amount,
                'order_id' => $metadata['order_id'],
                'service_name' => $metadata['service_name'],
            ])
            ->send();

        if (!$response->isOk) {
            throw new ServerErrorHttpException('Ошибка Paykeeper: ' . $response->content);
        }

        return $response->data['payment_url'];
    }
}