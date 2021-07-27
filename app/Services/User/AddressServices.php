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
}
