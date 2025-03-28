<?php

namespace common\bitrix24\order\type;

use common\bitrix24\order\storage\StorageInterface;
use common\bitrix24\user\helper\UserBitrixHelper;
use frontend\components\Bx24Handler;

class UserType
{
    private string $username;
    private ?string $first_name;
    private ?int $user_id;
    private ?int $bx_id;
    private int $secure_status;
    private int $balance;
    private int $deposit;
    private StorageInterface $storage;
    private array $arItem = [];
    private ?string $phoneMask = null;

    const TYPE_SECURE_ALL_USERS = 1;
    const TYPE_SECURE_NEW_USERS = 2;
    const TYPE_SECURE_OLD_USERS = 3;
    /**
     * @var true
     */
    private bool $isGuest = false;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;

        $this->loadItem();
        $this->setProperty();

        if($this->isUserWithoutBxId()){
            $this->registerUserBx24();
            $this->updateUserInfo();
        }elseif($this->isExitBxId()){
            $this->updateInfoBalance();
        }
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getBxId(): ?int
    {
        return $this->bx_id;
    }

    public function getSecureStatus(): int
    {
        return $this->secure_status;
    }

    public function isNewUser(): bool
    {
        return $this->secure_status === self::TYPE_SECURE_NEW_USERS;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getDeposit(): int
    {
        return $this->deposit;
    }

    public function isNotGuest(): bool
    {
        return $this->isGuest === false;
    }

    public function getPhoneWithMask(): string
    {
        if($this->phoneMask !== null){
            return $this->phoneMask;
        }

        $from = preg_replace('![^0-9]+!', '', $this->getUsername());

        $this->phoneMask = '+'.sprintf("%s (%s) %s-%s-%s",
                substr($from, 0, 1),
                substr($from, 1, 3),
                substr($from, 4, 3),
                substr($from, 7, 2),
                substr($from, 9)
            );

        return $this->phoneMask;
    }
    private function initUserBitrixHelper(): UserBitrixHelper
    {
        return new UserBitrixHelper(new Bx24Handler('+'.$this->username));
    }

    private function updateInfoBalance()
    {
        $userBitrixHelper = $this->initUserBitrixHelper();

        $userBitrixHelper->updateInfoBalanceByBitrix24($this->bx_id);

        $this->balance = $userBitrixHelper->getBalanceBonus();
        $this->deposit = $userBitrixHelper->getBalanceDeposit();
    }

    private function registerUserBx24()
    {
        $userBitrixHelper = $this->initUserBitrixHelper();

        if (!$userBitrixHelper->registerUserBx24()) {
            throw new \RuntimeException('Не удалось получить данные для пользователя из б24.');
        }

        $this->bx_id = $userBitrixHelper->getBxId();
        $this->secure_status = $userBitrixHelper->getSecureStatus();
        $this->balance = $userBitrixHelper->getBalanceBonus();
        $this->deposit = $userBitrixHelper->getBalanceDeposit();
    }

    private function updateUserInfo()
    {
        if(isset($this->username, $this->bx_id, $this->secure_status, $this->user_id)){
            $this->storage->save(
                [
                    'username' => $this->username,
                    'user_id' => $this->user_id,
                    'bx_id' => $this->bx_id,
                    'secure_status' => $this->secure_status,
                    'balance' => $this->balance,
                    'deposit' => $this->deposit,
                ]
            );
        }
    }

    private function isUserWithoutBxId(): bool
    {
        return (isset($this->user_id) && !$this->isGuest) && !isset($this->bx_id);
    }

    private function isExitBxId(): bool
    {
        return (isset($this->user_id) && !$this->isGuest) && isset($this->bx_id);
    }

    protected function loadItem()
    {
        if (empty($this->arItem)) {
            $this->arItem = $this->storage->load();
        }
    }

    private function setProperty()
    {
        if (!empty($this->arItem)) {
            $this->username = $this->arItem['username'];
            $this->first_name = $this->arItem['first_name'];
            $this->isGuest = $this->arItem['isGuest'];
            $this->user_id = $this->arItem['user_id'] ?? null;
            $this->bx_id = $this->arItem['bx_id'] ?? null;
            $this->secure_status = $this->arItem['secure_status'] ?? self::TYPE_SECURE_NEW_USERS;
            $this->balance = $this->arItem['balance'] ?? 0;
            $this->deposit = $this->arItem['deposit'] ?? 0;
        }
    }
}