<?php

namespace common\bitrix24\order\type;

use common\bitrix24\order\storage\StorageInterface;

class OrderType
{
    private int $id;
    private int $deal_id;
    private ?int $user_id;
    private ?int $lead_id;
    private ?int $program_id;
    private ?int $count_day;
    private float $sum;
    private int $status = 0;
    private int $created_at;
    private int $updated_at;
    private ?string $promocode;
    private ?int $region_id;
    private ?string $delivery_date;
    private ?string $delivery_time;
    private StorageInterface $storage;
    private array $arItem = [];
    const NO_PAID_STATUS = 0;
    const PAID_STATUS = 1;

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
            'deal_id' => $this->deal_id,
            'user_id' => $this->user_id,
//            'lead_id' => $this->lead_id,
            'program_id' => $this->program_id,
            'count_day' => $this->count_day,
            'sum' => $this->sum,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'promocode' => $this->promocode,
            'region_id' => $this->region_id,
            'delivery_date' => $this->delivery_date,
            'delivery_time' => $this->delivery_time,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDealId(): int
    {
        return $this->deal_id;
    }

    public function save()
    {
        $this->storage->save($this->getProperty());
        $this->id = $this->storage->getId();
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