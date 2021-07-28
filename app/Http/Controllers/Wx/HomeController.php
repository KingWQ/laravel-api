<?php

namespace App\Http\Controllers\Wx;

class GrouponController extends WxController
{
    protected $only = [];

    //二维码上的地址是固定的，可以通过配置灵活跳转
    public function redirectShareUrl()
    {
        $type = $this->verifyString('type','groupon');
        $id = $this->verifyId('id');

        if($type == 'groupon'){
            return redirect()->to(env('H5_URL').'/#/items/detail/'.$id);
        }

        return redirect()->to(env('H5_URL').'/#/items/detail/'.$id);

    }
}
