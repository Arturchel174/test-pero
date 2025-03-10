<?php

namespace common\bitrix24\order\type;

use common\bitrix24\order\storage\StorageInterface;

class ProgramType
{
    private int $id;
    private int $program_id;
    private ?int $min_count_day;
    private int $count_day;
    private int $calories;
    private float $price;
    private ?float $price_fake;
    private float $price_day;
    private ?float $price_first;
    private ?float $price_min;
    private string $program_name;
    private ?string $description;
    private StorageInterface $storage;
    private array $arItem = [];
    public const CURRENCY_ID = 'RUB';

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;

        $this->loadItem();
        $this->setProperty();
    }

    public function getProgramId(): int
    {
        return $this->program_id;
    }

    public function setProgramId(int $program_id): void
    {
        $this->program_id = $program_id;
    }

    public function getCountDay(): int
    {
        return $this->count_day;
    }

    public function setCountDay(int $count_day): void
    {
        $this->count_day = $count_day;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getProgramName(): string
    {
        return $this->program_name;
    }

    public function getCalories(): int
    {
        return $this->calories;
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
            $this->program_id = $this->arItem['program_id'];
            $this->count_day = $this->arItem['count_day'];
            $this->program_name = $this->arItem['program_name'];
            $this->calories = $this->arItem['calories'];
            $this->min_count_day = $this->arItem['min_count_day'];
            $this->price_min = $this->arItem['price_min'];
            $this->price = $this->arItem['price'];
            $this->price_fake = $this->arItem['price_fake'];
            $this->price_day = $this->arItem['price_day'];
            $this->price_first = $this->arItem['price_first'];
            $this->description = $this->arItem['description'];
        }
    }
}