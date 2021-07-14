<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WxController extends Controller
{
    protected $only;
    protected $except;

    public function __construct()
    {
        $option = [];
        if (!is_null($this->only)) {
            $option['only'] = $this->only;
        }
        if (!is_null($this->except)) {
            $option['except'] = $this->except;
        }

        $this->middleware('auth:wx', $option);
    }


    protected function codeReturn(array $codeResponse, $data = null, $info = '')
    {
        [$errno, $errmsg] = $codeResponse;
        $ret = ['errno' => $errno, 'errmsg' => $info ? : $errmsg];
        if (!is_null($data)) {
            $ret['data'] = $data;
        }

        return response()->json($ret);
    }

    protected function success($data = null)
    {
        return $this->codeReturn(CodeResponse::SUCCESS, $data);
    }

    protected function fail(array $codeResponse = CodeResponse::FAIL, $info = '')
    {
        return $this->codeReturn($codeResponse, null, $info);
    }

    protected function failOrSuccess($isSuccess, array $codeResponse = CodeResponse::FAIL, $data = null, $info = '')
    {
        if ($isSuccess){
            return $this->success($data);
        }
        return $this->fail($codeResponse, $info);
    }

    /**
     * @return User|null
     */
    public function user()
    {
        return Auth::guard('wx')->user();
    }

}
