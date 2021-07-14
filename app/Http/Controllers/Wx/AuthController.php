<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Models\User;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends WxController
{
    protected $only = ['user'];

    public function user()
    {
        $user = Auth::guard('wx')->user();

        return $this->success($user);
    }

    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (empty($username) || empty($password)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        $user = UserServices::getInstance()->getByUsername($username);
        if (is_null($user)) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT);
        }
        $isPass = Hash::check($password, $user->getAuthPassword());
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT, '用户名或密码错误');
        }

        $user->last_login_time = now()->toDateString();
        $user->last_login_ip = $request->getClientIp();
        if (!$user->save()) {
            return $this->fail(CodeResponse::UPDATED_FAIL);
        }

        $token = Auth::guard('wx')->login($user);

        return $this->success([
            'token'    => $token,
            'userInfo' => [
                'nickName'  => $username,
                'avatarUrl' => $user->avatar,
            ]
        ]);
    }

    public function register(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        $user = UserServices::getInstance()->getByUsername($username);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_NAME_REGISTERED);
        }

        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);
        }
        $user = UserServices::getInstance()->getByMobile($mobile);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }

        UserServices::getInstance()->checkCaptcha($mobile, $code);

        $avatarUrl = "https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64";

        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = $avatarUrl;
        $user->nickname = $username;
        $user->last_login_time = Carbon::now()->toDateString();
        $user->last_login_ip = $request->getClientIp();
        $user->save();

        // todo 新用户发券

        return $this->success([
            'token'    => '',
            'userInfo' => [
                'nickName' => $username, 'avatarUrl' => $avatarUrl,
            ]
        ]);
    }

    public function regCaptcha(Request $request)
    {
        $mobile = $request->input('mobile');

        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);
        }

        $user = UserServices::getInstance()->getByMobile($mobile);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }

        $lock = Cache::add('register_captcha_lock_'.$mobile, 1, 60);
        if (!$lock) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY);
        }

        $isPass = UserServices::getInstance()->checkMobileSendCaptchaCount($mobile);
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY, '验证码当天发送不能超过10次');
        }

        $code = UserServices::getInstance()->setCaptcha($mobile);
        UserServices::getInstance()->sendCaptchaMsg($mobile, $code);

        return $this->success();
    }
}
