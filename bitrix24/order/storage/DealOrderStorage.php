<?php

namespace common\bitrix24\order\storage;

use common\bitrix24\order\type\OrderType;
use common\models\Orders;
use common\types\DealBx24;

class DealOrderStorage extends YiiOrderStorage implements StorageInterface
{
    private DealBx24 $dealBx24;
    private ?int $region_id;

    /**
     * @param DealBx24 $dealBx24
     */
    public function __construct(DealBx24 $dealBx24, int $user_id, int $region_id)
    {
        $this->dealBx24 = $dealBx24;
        $this->user_id = $user_id;
        $this->region_id = $region_id;

        if($this->dealBx24->id <= 0){
            throw new \RuntimeException('Сделка не была создана в б24!');
        }
    }


    public function load(): array
    {
        if(!empty($this->dealBx24)){
            return [
                'deal_id' => $this->dealBx24->id,
                'user_id' => $this->user_id,
                'program_id' => $this->dealBx24->program_id,
                'count_day' => $this->dealBx24::DAYS[$this->dealBx24->count_days],
                'sum' => round($this->dealBx24->opportunity),
                'promocode' => $this->dealBx24->promo_code ?? null,
                'delivery_date' => $this->dealBx24->delivery_date,
                'delivery_time' => $this->dealBx24::DELIVERY_TIME[$this->dealBx24->delivery_time],
                'status' => OrderType::NO_PAID_STATUS,
                'region_id' => $this->region_id,
                'created_at' => time(),
                'updated_at' => time()
            ];
        }else{
            throw new \RuntimeException('Заказ не сконфигурирован!');
        }
    }

}