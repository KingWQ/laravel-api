<?php

namespace Tests\Feature;

use App\Models\Address;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AddressTest extends TestCase
{
    //测试数据不插入数据库
    use DatabaseTransactions;

    public function testList()
    {
        $response3 = $this->get('wx/address/list', $this->getAuthHeader());
        $response3->assertJson(['errno' => 0]);
    }

    public function testDelete()
    {
        $address = Address::query()->first();
        $this->assertNotEmpty($address);
        $response = $this->post('wx/address/delete',['id'=>$address->id],$this->getAuthHeader());
        $response->assertJson(['errno'=>0]);
        $address = Address::query()->find($address->id);
        $this->assertEmpty($address);
    }

    public function testDetail()
    {
        $response = $this->get('wx/address/detail?id=1',$this->getAuthHeader());
        $response->assertJson(['errno'=>0]);
    }


}
