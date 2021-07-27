<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use App\ValidateRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;


class WxController extends Controller
{
    use ValidateRequest;

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
//            if(is_array($data)){
//                $data = array_filter($data, function ($item){
//                    return $item != null;
//                });
//            }
            $ret['data'] = $data;
        }

        return response()->json($ret);
    }

    protected function success($data = null)
    {
        return $this->codeReturn(CodeResponse::SUCCESS, $data);
    }

    protected function successPaginate($page)
    {
        return $this->success($this->paginate($page));
    }

    protected function fail(array $codeResponse = CodeResponse::FAIL, $info = '')
    {
        return $this->codeReturn($codeResponse, null, $info);
    }

    protected function failOrSuccess($isSuccess, array $codeResponse = CodeResponse::FAIL, $data = null, $info = '')
    {
        if ($isSuccess) {
            return $this->success($data);
        }

        return $this->fail($codeResponse, $info);
    }


    //登录用户
    public function user()
    {
        return Auth::guard('wx')->user();
    }

    //登录用户id
    public function userId()
    {
        return $this->user()->getAuthIdentifier();
    }

    //是否登录
    public function isLogin()
    {
        return !is_null($this->user());
    }


    //自定义分页返回数据格式
    protected function paginate($page, $list = null)
    {
        if ($page instanceof LengthAwarePaginator) {
            $total = $page->total();

            return [
                'total' => $total == 0 ? 0 : $page->total(),
                'page'  => $total == 0 ? 0 : $page->currentPage(),
                'limit' => $page->perPage(),
                'pages' => $total == 0 ? 0 : $page->lastPage(),
                'list'  => $list ?? $page->items(),
            ];
        }

        if ($page instanceof Collection) {
            $page = $page->toArray();
        }

        if (!is_array($page)) {
            return $page;
        }

        $total = count($page);

        return [
            'total' => $total,
            'page'  => $total == 0 ? 0 : 1,
            'limit' => $total,
            'pages' => $total == 0 ? 0 : 1,
            'list'  => $page,
        ];
    }


}
