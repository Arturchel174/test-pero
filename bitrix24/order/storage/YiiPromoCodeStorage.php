<?php

namespace common\bitrix24\order\storage;

use backend\models\CountDaysProgram;
use backend\models\Programs;
use backend\models\PromoCodes;

class YiiPromoCodeStorage implements StorageInterface
{
    private string $promo_code_name;
    private int $status;
    const STATUS_ACTIVE = 1;
    private ?PromoCodes $_promoCode;

    /**
     * @param string $promo_code
     */
    public function __construct(string $promo_code, int $status = self::STATUS_ACTIVE)
    {
        $this->promo_code_name = trim($promo_code);
        $this->status = $status;
    }


    public function load(): array
    {
        $this->_promoCode = PromoCodes::findOne([
            'code' => $this->promo_code_name,
            'status' => $this->status
        ]);

        if(isset($this->_promoCode)){
            $promoCodeType = $this->_promoCode->type;
        }

        if(isset($promoCodeType)){
            return [
                'promo_code_id' => $this->_promoCode->id,
                'promo_code_name' => $this->_promoCode->code,
                'user_id' => $this->_promoCode->user_id,
                'username' => $this->_promoCode->user->username,
                'secure_status' => $this->_promoCode->secure,
                'count_usage' => $this->_promoCode->count_usage,
                'create_date' => $this->_promoCode->created_at,
                'type_id' => $this->_promoCode->type_id,
                'close_date' => $promoCodeType->close_date,
                'type_name' => $promoCodeType->type,
                'limit_usage' => $promoCodeType->limit_usage,
            ];
        }else{
            throw new \RuntimeException('Промокод не найден!');
        }
    }

    public function getFiledValues($count_day)
    {
        return $this->_promoCode->getTypeSalePromocodeFieldValues($count_day);
    }

    public function save($item)
    {
        // TODO: Implement save() method.
    }
}