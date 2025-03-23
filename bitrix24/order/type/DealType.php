<?php

namespace common\bitrix24\order\type;

use common\bitrix24\order\storage\StorageInterface;
use frontend\components\Bx24Handler;

class DealType
{
    private ?int $id;
    private $program_name;
    private $username;
    private $title;
    private $opportunity;
    private $currency;
    private ?int $contact_id;
    private $count_days;
    private $delivery_time;
    private $preferred_time;
    private $category_id;
    private $stage_id;
    private $city_id;
    private $pay_info;
    private $source_id;
    private $assigned_by_id;
    private $promo_code;
    private $promocode_sale;
    private $promocode_days;
    private $promocode_string;
    private StorageInterface $storage;
    private array $arItem = [];

    const FIRST_ORDER_TEXT = ' — Первый заказ';
    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;

        $this->loadItem();
        $this->setProperty();
    }

    public function getProperty(): array
    {
        return [
            'id' => $this->id,
            'program_name' => $this->program_name,
            'username' => $this->username,
            'title' => $this->title,
            'opportunity' => $this->opportunity,
            'currency' => $this->currency,
            'contact_id' => $this->contact_id,
            'count_days' => $this->count_days,
            'delivery_time' => $this->delivery_time,
            'preferred_time' => $this->preferred_time,
            'category_id' => $this->category_id,
            'stage_id' => $this->stage_id,
            'city_id' => $this->city_id,
            'pay_info' => $this->pay_info,
            'source_id' => $this->source_id,
            'assigned_by_id' => $this->assigned_by_id,
            'promo_code' => $this->promo_code,
            'promocode_sale' => $this->promocode_sale,
            'promocode_days' => $this->promocode_days,
            'promocode_string' => $this->promocode_string,
        ];
    }

    /**
     * @return mixed
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getContactId(): ?int
    {
        return $this->contact_id;
    }


    /**
     * @return mixed
     */
    public function getProgramName()
    {
        return $this->program_name;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getOpportunity()
    {
        return $this->opportunity;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    public function createTitle(): void
    {
        $this->title = "{$this->username} — {$this->program_name}";
    }

    public function addTextForTitle($text)
    {
        $this->title .= $text;
    }

    /**
     * @param mixed $opportunity
     */
    public function setOpportunity($opportunity): void
    {
        $this->opportunity = $opportunity;
    }

    /**
     * @param mixed $category_id
     */
    public function setCategoryId($category_id): void
    {
        $this->category_id = $category_id;
    }

    /**
     * @param mixed $stage_id
     */
    public function setStageId($stage_id): void
    {
        $this->stage_id = $stage_id;
    }

    /**
     * @param mixed $city_id
     */
    public function setCityId($city_id): void
    {
        $this->city_id = $city_id;
    }

    /**
     * @param mixed $pay_info
     */
    public function setPayInfo($pay_info): void
    {
        $this->pay_info = $pay_info;
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
            foreach ($this->arItem as $property => $item){
                $this->$property = $item;
            }
        }
    }
}