<?php

namespace common\bitrix24\order\type;

class RegionType
{
    private int $currentRegionId;
    private ?int $status_category;
    private ?string $deal_status_new;
    private ?int $deal_city_id;

    /**
     * @param int $currentRegionId
     * @param int|null $status_category
     * @param string|null $deal_status_new
     * @param int|null $deal_city_id
     */
    public function __construct(int $currentRegionId, ?int $status_category, ?string $deal_status_new, ?int $deal_city_id)
    {
        $this->currentRegionId = $currentRegionId;
        $this->status_category = $status_category;
        $this->deal_status_new = $deal_status_new;
        $this->deal_city_id = $deal_city_id;
    }

    public function getCurrentRegionId(): int
    {
        return $this->currentRegionId;
    }

    public function getStatusCategory(): ?int
    {
        return $this->status_category;
    }

    public function getDealStatusNew(): ?string
    {
        return $this->deal_status_new;
    }

    public function getDealCityId(): ?int
    {
        return $this->deal_city_id;
    }

}