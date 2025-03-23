<?php

namespace common\bitrix24\user\helper;

use frontend\components\Bx24Handler;

class UserBitrixHelper
{
    public Bx24Handler $bx24Handler;
    private int $bx_id;
    private string $user_name;
    private int $secure_status;
    /**
     * @var int|mixed
     */
    private int $balance_bonus;
    /**
     * @var int|mixed
     */
    private int $balance_deposit;

    /**
     * @param Bx24Handler $bx24Handler
     */
    public function __construct(Bx24Handler $bx24Handler)
    {
        $this->bx24Handler = $bx24Handler;
        $this->loadSecureStatus();
    }

    public function setBxId(int $bx_id)
    {
        $this->bx_id = $bx_id;
    }

    private function isUserExist(): bool
    {
        return isset($this->bx24Handler->userId);
    }

    public function updateInfoBalanceByBitrix24($bx_id)
    {
        if($bx_id > 0){
            $userBx24 = $this->bx24Handler->getContactByID($bx_id);

            $this->balance_bonus = (int) $userBx24["UF_CRM_1644550002765"] ?? 0;
            $this->balance_deposit = (int) $userBx24["UF_CRM_1742298126113"] ?? 0;
        }
    }

    public function registerUserBx24(): bool
    {
        $userBx24 = $this->bx24Handler->RegUser();

        $this->bx_id = $userBx24['ID'] ?? $this->bx24Handler->userId;
        $this->user_name = $userBx24['NAME'] ?? $this->bx24Handler->userName;
        $this->balance_bonus = $userBx24["UF_CRM_1644550002765"] ?? 0;
        $this->balance_deposit = $userBx24["UF_CRM_1742298126113"] ?? 0;

        return isset($this->bx_id, $this->user_name);
    }

    public function sendRefPromoCode(array $fields): void
    {
        $this->bx24Handler->addLists($fields);
    }

    public function sendPromoCodeInContact(string $promoCode): void
    {
        $this->bx24Handler->bx24->updateContact($this->bx_id, [
            'UF_CRM_1729072631416' => $promoCode,
        ]);
    }

    private function loadSecureStatus()
    {
        $this->bx24Handler->getSecureStatus();

        $this->secure_status = $this->bx24Handler->status;
    }

    public function getSecureStatus(): int
    {
        return $this->secure_status;
    }

    /**
     * @return int
     */
    public function getBxId(): int
    {
        return $this->bx_id;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->user_name;
    }

    /**
     * @return int
     */
    public function getBalanceBonus(): int
    {
        return $this->balance_bonus;
    }

    public function getBalanceDeposit(): int
    {
        return $this->balance_deposit;
    }


}