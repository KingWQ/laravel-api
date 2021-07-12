<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return ['errno' => 707, 'errmsg' => '手机号格式不正确'];
        }
        $user = (new UserServices())->getByMobile($mobile);
        if (!is_null($user)) {
            return ['errno' => 705, 'errmsg' => '手机号已注册'];
        }

        $isPass = (new UserServices())->checkCaptcha($mobile, $code);
        if(!$isPass){
            return ['errno' => 703, 'errmsg' => '短信验证码不正确'];
        }

        $avatarUrl = "https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64";

        $user                  = new User();
        $user->username        = $username;
        $user->password        = Hash::make($password);
        $user->mobile          = $mobile;
        $user->avatar          = $avatarUrl;
        $user->nickname        = $username;
        $user->last_login_time = Carbon::now()->toDateString();
        $user->last_login_ip   = $request->getClientIp();
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

    public function regCaptcha(Request $request)
    {
        $mobile = $request->input('mobile');

        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return ['errno' => 707, 'errmsg' => '手机号格式不正确'];
        }

        $user = (new UserServices())->getByMobile($mobile);
        if (!is_null($user)) {
            return ['errno' => 705, 'errmsg' => '手机号已注册'];
        }

        $lock = Cache::add('register_captcha_lock_'.$mobile, 1, 60);
        if (!$lock) {
            return ['errno' => 702, 'errmsg' => '验证码未超时1分钟，不能发送'];
        }

        $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
        if(!$isPass){
            return ['errno' => 702, 'errmsg' => '验证码当天发送不能超过10次'];
        }

        $code = (new UserServices())->setCaptcha($mobile);
        (new UserServices())->sendCaptchaMsg($mobile, $code);

        return ['errno' => 0, 'errmsg' => '成功', 'data'=>null];
    }
}
