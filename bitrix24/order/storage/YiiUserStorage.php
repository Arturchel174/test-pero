<?php

namespace common\bitrix24\order\storage;

use common\bitrix24\order\type\UserType;
use common\models\User;
use frontend\models\UserInfo;
use yii\db\Exception;

class YiiUserStorage implements StorageInterface
{
    private string $username;
    private bool $isGuest;

    /**
     * @param string $username
     */
    public function __construct(string $username, bool $isGuest = true)
    {
        $this->username = preg_replace('![^0-9]+!', '', $username);
        $this->isGuest = $isGuest;
    }

    public function load(): array
    {
        $userInfo = UserInfo::findByUserPhone($this->username);

        $arItem = [];

        if($this->isGuest){
            $arItem = [
                'username' => $this->username,
                'isGuest' => $this->isGuest,
            ];
        }

        if(isset($userInfo)){
            $arItem = [
                'username' => $this->username,
                'first_name' => $userInfo->first_name,
                'isGuest' => false,
                'user_id' => $userInfo->user_id ?? null,
                'bx_id' => $userInfo->bx_id ?? null,
                'secure_status' => $userInfo->user->secure_status ?? null,
                'balance' => $userInfo->balance ?? 0,
                'deposit' => $userInfo->deposit ?? 0,
            ];
        }

        return $arItem;
    }

    /**
     * @throws Exception
     */
    public function save($item)
    {
        $user = User::findOne($item["user_id"]);
        $user->secure_status = $item["secure_status"];

        $userInfo = UserInfo::findOne(['user_id' => $user->id]);
        $userInfo->bx_id = $item["bx_id"];
        $userInfo->balance = $item["balance"];
        $userInfo->deposit = $item["deposit"];

        if(!($user->save() && $userInfo->save())){
            throw new \RuntimeException('Не удалось обновить данные для пользователя!');
        }
    }
}