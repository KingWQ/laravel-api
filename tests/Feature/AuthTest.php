<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    //测试数据不插入数据库
    use DatabaseTransactions;

    //测试正常注册逻辑

    /**
     * @group register
     */
    public function testRegister()
    {
        $response = $this->post('wx/auth/register', [
            'username' => 'huge2', 'password' => '123456', 'mobile' => '17828281233', 'code' => '1234'
        ]);
        $response->assertStatus(200);
        $ret = $response->getOriginalContent();
        $this->assertEquals(0, $ret['errno']);
        $this->assertNotEmpty($ret['data']);
    }

//    public function testRegisterMobile()
//    {
//        $response = $this->post('wx/auth/register', [
//            'username' => 'huge3', 'password' => '123456', 'mobile' => '17828281233', 'code' => '1234'
//        ]);
//        $response->assertStatus(200);
//        $ret = $response->getOriginalContent();
//        $this->assertEquals(707, $ret['errno']);
//    }

    public function testRegCaptcha()
    {
        $response = $this->post('wx/auth/regCaptcha', ['mobile' => '17828281233']);
        $response->assertJson(['errno' => 0, 'errmsg' => '成功', 'data' => null]);
        $response = $this->post('wx/auth/regCaptcha', ['mobile' => '17828281233']);
        $response->assertJson(['errno' => 702, 'errmsg' => '验证码未超时1分钟，不能发送']);
    }
}
