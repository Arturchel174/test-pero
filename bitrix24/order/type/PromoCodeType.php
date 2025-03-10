<?php

namespace common\bitrix24\order\type;

use common\bitrix24\order\storage\StorageInterface;

class PromoCodeType
{
    private string $promo_code_name;
    private string $type_name;
    private ?int $user_id;
    private ?string $username;
    private int $promo_code_id;
    private int $secure_status;
    private int $type_id;
    private string $close_date;
    private string $create_date;
    private int $count_usage;
    private int $limit_usage;
    private StorageInterface $storage;
    private array $arItem = [];
    private bool $flagValidate = false;
    private string $owner = 'Delifood';
    private $valueDiscount = null;
    private $typeDiscount = null;
    
    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;

        $this->loadItem();
        $this->setProperty();
    }

    public function isExitPromoCode(): bool
    {
        return isset($this->promo_code_id, $this->type_id);
    }

    public function getSalePromoCodeFieldValues($count_day)
    {
        if($this->isExitPromoCode()){
            return $this->storage->getFiledValues($count_day);
        }

        return [];
    }
    
    public function getPromoCodeName(): string
    {
        return $this->promo_code_name;
    }

    public function getTypeName(): string
    {
        return $this->type_name;
    }

    public function getPromoCodeId(): int
    {
        return $this->promo_code_id;
    }

    public function getSecureStatus(): int
    {
        return $this->secure_status;
    }

    public function getTypeId(): int
    {
        return $this->type_id;
    }

    public function getCloseDate(): string
    {
        return $this->close_date;
    }

    public function getCreateDate(): string
    {
        return $this->create_date;
    }

    public function getCountUsage(): int
    {
        return $this->count_usage;
    }

    public function getLimitUsage(): int
    {
        return $this->limit_usage;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function isFlagValidate(): bool
    {
        return $this->flagValidate;
    }

    public function setFlagValidate(bool $flagValidate): void
    {
        $this->flagValidate = $flagValidate;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return null
     */
    public function getValueDiscount()
    {
        return $this->valueDiscount;
    }

    /**
     * @param null $valueDiscount
     */
    public function setValueDiscount($valueDiscount): void
    {
        $this->valueDiscount = $valueDiscount;
    }

    /**
     * @return null
     */
    public function getTypeDiscount()
    {
        return $this->typeDiscount;
    }

    /**
     * @param null $typeDiscount
     */
    public function setTypeDiscount($typeDiscount): void
    {
        $this->typeDiscount = $typeDiscount;
    }

    private function loadItem()
    {
        if (empty($this->arItem)) {
            $this->arItem = $this->storage->load();
        }
    }

    private function setProperty()
    {
        if (!empty($this->arItem)) {
            $this->promo_code_id = $this->arItem['promo_code_id'];
            $this->promo_code_name = $this->arItem['promo_code_name'];
            $this->user_id = $this->arItem['user_id'];
            $this->username = $this->arItem['username'];
            $this->secure_status = $this->arItem['secure_status'];
            $this->count_usage = $this->arItem['count_usage'];
            $this->create_date = $this->arItem['create_date'];
            $this->type_id = $this->arItem['type_id'];
            $this->close_date = $this->arItem['close_date'];
            $this->type_name = $this->arItem['type_name'];
            $this->limit_usage = $this->arItem['limit_usage'];
        }
    }

}