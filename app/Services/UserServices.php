<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\VerificationCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class UserServices
{
    /**
     * 根据用户名获取用户
     * @param $username
     * @return User|null|Model
     */
    public function getByUsername($username)
    {
        return User::query()->where('username', $username)->where('deleted', 0)->first();
    }

    /**
     * 根据手机号获取用户
     * @param $mobile
     * @return User|null|Model
     */
    public function getByMobile($mobile)
    {
        return User::query()->where('mobile', $mobile)->where('deleted', 0)->first();
    }

    /**
     * 检查验证码每天发送的次数
     * @param  string  $mobile
     * @return bool
     */
    public function checkMobileSendCaptchaCount(string $mobile)
    {
        $countKey = 'register_captcha_count'.$mobile;
        if (Cache::has($countKey)) {
            $count = Cache::increment($countKey);
            if ($count > 10) {
                return false;
            }
        } else {
            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        }

        return true;
    }


    /**
     * 设置短信验证码
     * @param  string  $mobile
     * @return int|string
     * @throws \Exception
     */
    public function setCaptcha(string $mobile)
    {
        $code = random_int(100000, 999999);
        $code = strval($code);
        Cache::put('register_captcha_'.$mobile, $code, 600);

        return $code;
    }

    /**
     * 检查验证码
     * @param  string  $mobile
     * @param  string  $code
     * @return bool
     */
    public function checkCaptcha(string $mobile, string $code)
    {
        $key = 'register_captcha_'.$mobile;
        $isPass = $code === Cache::get($key);
        if ($isPass) {
            Cache::forget($key);
        }

        return $isPass;
    }

    /**
     * 发送短信验证码
     * @param  string  $mobile
     * @param  string  $code
     */
    public function sendCaptchaMsg(string $mobile, string $code)
    {
        if (app()->environment('testing')) {
            return;
        }
        Notification::route(EasySmsChannel::class, new PhoneNumber($mobile, 86))->notify(new VerificationCode($code));
    }
}
