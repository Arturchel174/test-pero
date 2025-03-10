<?php

namespace common\bitrix24\order\storage;

interface StorageInterface
{
    /**
     * @return array $arItem
     */
    public function load(): array;

    /**
     * @param $item
     */
    public function save($item);
}