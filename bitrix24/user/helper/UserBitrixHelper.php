<?php

namespace common\bitrix24\user\helper;

use frontend\components\Bx24Handler;

class UserBitrixHelper
{
    public Bx24Handler $bx24Handler;
    private $bx_id;
    private $user_name;
    private $secure_status;

    /**
     * @param Bx24Handler $bx24Handler
     */
    public function __construct(Bx24Handler $bx24Handler)
    {
        $this->bx24Handler = $bx24Handler;
        $this->loadSecureStatus();
    }

    public function isUserExist(): bool
    {
        return isset($this->bx24Handler->userId);
    }

    public function registerUserBx24(): bool
    {
        if(!$this->isUserExist()){
            $userBx24 = $this->bx24Handler->RegUser();
        }

        $this->bx_id = $userBx24['ID'] ?? $this->bx24Handler->userId;
        $this->user_name = $userBx24['NAME'] ?? $this->bx24Handler->userName;

        return isset($this->bx_id, $this->user_name);
    }

    private function loadSecureStatus()
    {
        $this->bx24Handler->getSecureStatus();

        $this->secure_status = $this->bx24Handler->status;
    }

    public function getSecureStatus()
    {
        return $this->secure_status;
    }

    /**
     * @return mixed
     */
    public function getBxId()
    {
        return $this->bx_id;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->user_name;
    }


}