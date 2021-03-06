<?php

namespace Tests;

use App\Models\Goods\GoodsProduct;
use App\Models\User\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user       = factory(User::class)->create();
    }

    protected function getAuthHeader($username='user123',$password='user123')
    {
        $response = $this->post('wx/auth/login', ['username' => $username, 'password' => $password]);
        $token = $response->getOriginalContent()['data']['token'];
        return ['Authorization' => "Bearer {$token}"];
    }
}
