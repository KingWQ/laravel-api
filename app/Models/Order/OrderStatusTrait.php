<?php

namespace App\Models\Order;




use App\Enums\OrderEnums;

trait OrderStatusTrait
{
    public function canCanelHandle()
    {
        return $this->order_status == OrderEnums::STATUS_CREATE;
    }

    public function canPayHandle()
    {
        return $this->order_status == OrderEnums::STATUS_CREATE;
    }

}
