<?php

    namespace Tests\Feature;

    use App\Services\UserServices;
    use Illuminate\Foundation\Testing\DatabaseTransactions;
    use Tests\TestCase;

    class AuthTest extends TestCase
    {
        //测试数据不插入数据库
        use DatabaseTransactions;

        //测试正常注册逻辑
        public function testRegister()
        {
            $code = UserServices::getInstance()->setCaptcha('17828281233');
            $response = $this->post('wx/auth/register', [
                'username' => 'huge2', 'password' => '123456', 'mobile' => '17828281233', 'code' => $code
            ]);
            $response->assertStatus(200);
            $ret = $response->getOriginalContent();
            $this->assertEquals(0, $ret['errno']);
            $this->assertNotEmpty($ret['data']);
        }

        public function testRegisterErrCOde()
        {
            $response = $this->post('wx/auth/register', [
                'username' => 'huge3', 'password' => '123456', 'mobile' => '17828281233', 'code' => '1234'
            ]);
            $response->assertJson(['errno' => 703, 'errmsg' => '验证码错误']);
        }

        public function testRegisterMobile()
        {
            $response = $this->post('wx/auth/register', [
                'username' => 'huge3', 'password' => '123456', 'mobile' => '17828281233', 'code' => '1234'
            ]);
            $response->assertStatus(200);
            $ret = $response->getOriginalContent();
            $this->assertEquals(703, $ret['errno']);
        }

        public function testRegCaptcha()
        {
            $response = $this->post('wx/auth/regCaptcha', ['mobile' => '17828281233']);
            $response->assertJson(['errno' => 0, 'errmsg' => '成功']);
            $response = $this->post('wx/auth/regCaptcha', ['mobile' => '17828281233']);
            $response->assertJson(['errno' => 702, 'errmsg' => '验证码未超时1分钟，不能发送']);
        }
        public function testLogin()
        {
            $response = $this->post('wx/auth/login', ['username' => 'user123','password'=>'user123']);
            $response->assertJson([
                "errno" => 0,
                "errmsg" => "成功",
                "data" =>  [
                    "userInfo" =>[
                        "nickName" => "user123",
                        "avatarUrl" => ""
                    ]
                ]
            ]);
            $this->assertNotEmpty($response->getOriginalContent()['data']['token'] ?? '');
        }

        public function testUser()
        {
            $response = $this->post('wx/auth/login', ['username' => 'user123','password'=>'user123']);
            $token = $response->getOriginalContent()['data']['token'];
            $response2 = $this->get('wx/auth/user',['Authorization'=>"Bearer {$token}"]);
            $response2->assertJson(['data'=>['username'=>'user123']]);

        }
    }
