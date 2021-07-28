<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function getAuthHeader($username='user123',$password='user123')
    {
        $response = $this->post('wx/auth/login', ['username' => $username, 'password' => $password]);
        $token = $response->getOriginalContent()['data']['token'];
        return ['Authorization' => "Bearer {$token}"];
    }
}
