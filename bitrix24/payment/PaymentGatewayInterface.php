<?php

namespace common\bitrix24\payment;

interface PaymentGatewayInterface
{
    /**
     * Генерирует платежную ссылку.
     * @param float $amount Сумма оплаты.
     * @param array $metadata Дополнительные данные (например, ID заказа).
     */
    public function generatePaymentLink(float $amount, array $metadata): string;
}