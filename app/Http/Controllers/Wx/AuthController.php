<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $mobile   = $request->input('mobile');
        $code     = $request->input('code');

        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return ['errno' => 401, 'errmsg' => '参数不对'];
        }

        $user = (new UserServices())->getByUsername($username);
        if (!is_null($user)) {
            return ['errno' => 704, 'errmsg' => '用户名已注册'];
        }
        $user = (new UserServices())->getByMobile($mobile);
        if (!is_null($user)) {
            return ['errno' => 705, 'errmsg' => '手机号已注册'];

        }

        // todo 验证验证码是否正确
        $avatarUrl = "https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64";

        $user                  = new User();
        $user->usename         = $username;
        $user->password        = Hash::make($password);
        $user->mobile          = $mobile;
        $user->avatar          = $avatarUrl;
        $user->nickname        = $username;
        $user->last_login_time = Carbon::now()->toDateString();
        $user->last_login_ip   = $request->getClientIp();
        $user->add_time        = Carbon::now()->toDateString();
        $user->update_time     = Carbon::now()->toDateString();
        $user->save();
        // todo 新用户发券
        return [
            'errno' => 0, 'errmsg' => '成功', 'data' => [
                'token' => '', 'userInfo' => [
                    'nickName' => $username, 'avatarUrl' => $avatarUrl,
                ]
            ]
        ];
    }
}
