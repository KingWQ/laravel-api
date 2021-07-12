<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;

class WxController extends Controller
{
    protected function codeReturn(array $codeResponse, $data = null, $info = '')
    {
        [$errno, $errmsg] = $codeResponse;
        $ret = ['errno' => $errno, 'errmsg' => $errmsg ? : $info];
        if (!is_null($data)) {
            $ret['data'] = $data;
        }

        return response()->json($ret);
    }

    protected function success($data=null)
    {
        return $this->codeReturn(CodeResponse::SUCCESS, $data);
    }

    protected function fail(array $codeResponse=CodeResponse::FAIL, $info='')
    {
        return $this->codeReturn($codeResponse, null, $info);
    }

}
