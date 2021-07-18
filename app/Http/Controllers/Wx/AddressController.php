<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Models\Address;
use App\Services\AddressServices;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddressController extends WxController
{
    public function list()
    {
        $list = AddressServices::getInstance()->getAddressListByUserId($this->user()->id);
        $list = $list->map(function (Address $address) {
            $address = $address->toArray();
            $item    = [];
            foreach ($address as $key => $value) {
                $key        = lcfirst(Str::studly($key));
                $item[$key] = $value;
            }

            return $item;
        });

        return $this->success([
            'total' => $list->count(),
            'page'  => 1,
            'list'  => $list,
            'pages' => 1,
            'limit' => $list->count(),
        ]);
    }

    public function detail()
    {

    }

    public function save()
    {

    }

    public function delete(Request $request)
    {
        $id = $request->input('id', 0);
        if(empty($id) && is_numeric($id)){
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        AddressServices::getInstance()->delete($this->user()->id, $id);
        return $this->success();
    }

}
