<?php

namespace App\Services\User;

use App\CodeResponse;
use App\Models\User\Address;
use App\Services\BaseServices;

class AddressServices extends BaseServices
{
    public function getAddressListByUserId(int $userId)
    {
        return Address::query()->where('user_id', $userId)->get();
    }

    //获取地址或返回默认底子
    public function getAddressOrDefault($userId, $addressId = null)
    {
        if (empty($addressId)) {
            $address   = AddressServices::getInstance()->getDefaultAddress($userId);
        } else {
            $address = AddressServices::getInstance()->getAddress($userId, $addressId);
            if (empty($address)) {
                $this->throwBadArgumentValue();
            }
        }
        return $address;
    }

    public function getAddress($userId, $addressId)
    {
        return Address::query()->where('user_id', $userId)->where('id', $addressId)->first();
    }

    /**
     * @param $userId
     * @param $addressId
     * @return bool|mixed|null
     * @throws \App\Exceptions\BusinessException
     */
    public function delete($userId, $addressId)
    {
        $address = $this->getAddress($userId, $addressId);
        if (is_null($address)) {
            $this->throwBusinessException(CodeResponse::PARAM_ILLEGAL);
        }

        return $address->delete();
    }

    public function detail($userId, $addressId)
    {
        $address = $this->getAddress($userId, $addressId);
        if (is_null($address)) {
            $this->throwBusinessException(CodeResponse::PARAM_ILLEGAL);
        }

        return $address;
    }

    public function getDefaultAddress($userId)
    {
        return Address::query()->where('user_id',$userId)
            ->where('is_default',1)->first();
    }
}
