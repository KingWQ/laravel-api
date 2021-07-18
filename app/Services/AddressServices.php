<?php

namespace App\Services;

use App\CodeResponse;
use App\Models\Address;

class AddressServices extends BaseServices
{
    public function getAddressListByUserId(int $userId)
    {
        return Address::query()->where('user_id', $userId)->where('deleted', 0)->get();
    }

    public function getAddress($userId, $addressId)
    {
        return Address::query()->where('user_id', $userId)->where('id',$addressId)->where('deleted',0)->first();
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
        if(is_null($address)){
            $this->throwBusinessException(CodeResponse::PARAM_ILLEGAL);
        }
        return $address->delete();
    }

    public function detail($userId, $addressId)
    {
        $address = $this->getAddress($userId, $addressId);
        if(is_null($address)){
            $this->throwBusinessException(CodeResponse::PARAM_ILLEGAL);
        }
        return $address;
    }
}
