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

    public function canShipHandle()
    {
        return $this->order_status == OrderEnums::STATUS_PAY;
    }

    public function canConfirmHandle()
    {
        return $this->order_status == OrderEnums::STATUS_SHIP;
    }

    public function canRefundHandle()
    {
        return $this->order_status == OrderEnums::STATUS_PAY;

    }

    public function canAgreeRefundHandle()
    {
        return $this->order_status == OrderEnums::STATUS_REFUND;
    }

    public function canDeleteHandle()
    {
        return in_array($this->order_status,[
            OrderEnums::STATUS_CANCEL,
            OrderEnums::STATUS_AUTO_CANCEL,
            OrderEnums::STATUS_ADMIN_CANCEL,
            OrderEnums::STATUS_REFUND_CONFIRM,
            OrderEnums::STATUS_CONFIRM,
            OrderEnums::STATUS_AUTO_CONFIRM
        ]);
    }


}
