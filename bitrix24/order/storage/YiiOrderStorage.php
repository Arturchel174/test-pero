<?php

namespace common\bitrix24\order\storage;

use common\models\Orders;
use yii\db\Exception;

class YiiOrderStorage implements StorageInterface
{
    private int $id;
    private int $deal_id;
    protected ?int $user_id;

    public function load(): array
    {
        // TODO: Implement load() method.
    }

    /**
     * @throws Exception
     */
    public function save($item)
    {
        $model = Orders::findOne(['deal_id' => $item['deal_id']]) ?? new Orders();

        $arAttributes['Orders'] = $item;

        $model->load($arAttributes);

        if(!$model->save()) {
            throw new \RuntimeException('Не удалось сохранить данные по заказу!');
        }

        $this->id = $model->id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}