<?php

namespace common\bitrix24\order\promo\validation;

use common\bitrix24\order\type\PromoCodeType;

class ValidationPromoCode
{
    /**
     * @var ValidationInterface[]
     */
    private $validators;

    public function __construct()
    {
        $validators = func_get_args();
        foreach ($validators as $validator) {
            if (!$validator instanceof ValidationInterface) {
                throw new \InvalidArgumentException('Invalid validator');
            }
        }
        $this->validators = $validators;
    }

    public function checkError(PromoCodeType $promoCodeType)
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            /* @var $validator ValidationInterface
             */
            $error = $validator->checkError($promoCodeType);

            if($error !== false){
                $errors[] = $error;
            }
        }

        if(!empty($errors)){
            return implode(",", $errors);
        }

        return false;
    }
}