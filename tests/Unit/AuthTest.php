<?php

namespace Tests\Unit;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Services\UserServices;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthTest extends TestCase
{
    //phpunit tests/Unit/AuthTest.php
    public function testCheckMobileSendCaptchaCount()
    {
        $mobile = '12245456767';
        foreach (range(0, 9) as $i) {
            $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
            $this->assertTrue($isPass);
        }
        $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
        $this->assertFalse($isPass);

        $countKey = 'register_captcha_count'.$mobile;
        Cache::forget($countKey);
        $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
        $this->assertTrue($isPass);
    }

    public function testCheckCaptCha()
    {
        $mobile = '12245456767';
        $code   = (new UserServices())->setCaptcha($mobile);
        $isPass = (new UserServices())->checkCaptcha($mobile, $code);
        $this->assertTrue($isPass);

        $this->expectExceptionObject(new BusinessException(CodeResponse::AUTH_CAPTCHA_UNMATCH));
         (new UserServices())->checkCaptcha($mobile, $code);

    }

}
