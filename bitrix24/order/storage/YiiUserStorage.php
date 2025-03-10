<?php

namespace common\bitrix24\order\storage;

use common\bitrix24\order\type\UserType;
use common\models\User;
use frontend\models\UserInfo;
use yii\db\Exception;

class YiiUserStorage implements StorageInterface
{
    private string $username;

    /**
     * @param string $username
     */
    public function __construct(string $username)
    {
        $this->username = preg_replace('![^0-9]+!', '', $username);
    }

    public function load(): array
    {
        $userInfo = UserInfo::findByUserPhone($this->username);

        if(isset($userInfo)){
            return [
                'username' => $this->username,
                'user_id' => $userInfo->user_id,
                'bx_id' => $userInfo->bx_id,
                'secure_status' => $userInfo->user->secure_status,
                'balance' => $userInfo->balance,
            ];
        }else{
            throw new \RuntimeException('Пользователь не найден!');
        }
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

        if(!($user->save() && $userInfo->save())){
            throw new \RuntimeException('Не удалось обновить данные для пользователя!');
        }
    }
}