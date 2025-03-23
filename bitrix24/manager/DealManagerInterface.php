<?php

namespace common\bitrix24\manager;

use common\types\DealBx24;

interface DealManagerInterface {
    public function createDeal(DealBx24 $deal): int;
    public function findExistingDeals(int $contactId): array;
    public function updateDeal(DealBx24 $deal): bool;
    public function initDaysWorkFlow(DealBx24 $deal);
}