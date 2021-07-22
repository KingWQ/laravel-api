<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * @return User|null
     */
    public function user()
    {
        return Auth::guard('wx')->user();
    }


    /**
     * 自定义分页返回数据格式
     */
    protected function paginate($page)
    {
        if ($page instanceof LengthAwarePaginator) {
            return [
                'total' => $page->total(),
                'page'  => $page->currentPage(),
                'limit' => $page->perPage(),
                'pages' => $page->lastPage(),
                'list'  => $page->items(),
            ];
        }

        if ($page instanceof Collection) {
            $page = $page->toArray();
        }

        if (!is_array($page)) {
            return $page;
        }

        return [
            'total' => count($page),
            'page'  => 1,
            'limit' => count($page),
            'pages' => 1,
            'list'  => $page,
        ];
    }


}
