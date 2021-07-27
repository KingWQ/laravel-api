<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Services\User\AddressServices;
use Illuminate\Http\Request;

class AddressController extends WxController
{
    public function list()
    {
        $list = AddressServices::getInstance()->getAddressListByUserId($this->user()->id);

        return $this->successPaginate($list);
    }

    public function detail(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id) && is_numeric($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        $address = AddressServices::getInstance()->detail($this->user()->id, $id);

        return $this->success($address);
    }

    public function save()
    {

    }

    public function delete(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id) && is_numeric($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        AddressServices::getInstance()->delete($this->user()->id, $id);

        return $this->success();
    }

}
