<?php

namespace App\Models\Order;




use App\Constant;
use App\Enums\OrderEnums;
use Illuminate\Support\Str;

trait OrderStatusTrait
{
    private $canHandleMap = [
        // 取消操作
        'cancel'      => [
            OrderEnums::STATUS_CREATE
        ],
        // 删除操作
        'delete'      => [
            OrderEnums::STATUS_CANCEL,
            OrderEnums::STATUS_AUTO_CANCEL,
            OrderEnums::STATUS_ADMIN_CANCEL,
            OrderEnums::STATUS_REFUND_CONFIRM,
            OrderEnums::STATUS_CONFIRM,
            OrderEnums::STATUS_AUTO_CONFIRM
        ],
        // 支付操作
        'pay'         => [
            OrderEnums::STATUS_CREATE
        ],
        // 发货
        'ship'        => [
            OrderEnums::STATUS_PAY
        ],
        // 评论操作
        'comment'     => [
            OrderEnums::STATUS_CONFIRM,
            OrderEnums::STATUS_AUTO_CONFIRM
        ],
        // 确认收货操作
        'confirm'     => [OrderEnums::STATUS_SHIP],
        // 取消订单并退款操作
        'refund'      => [OrderEnums::STATUS_PAY],
        // 再次购买
        'rebuy'       => [
            OrderEnums::STATUS_CONFIRM,
            OrderEnums::STATUS_AUTO_CONFIRM
        ],
        // 售后操作
        'aftersale'   => [
            OrderEnums::STATUS_CONFIRM,
            OrderEnums::STATUS_AUTO_CONFIRM
        ],
        // 同意退款
        'agreerefund' => [
            OrderEnums::STATUS_REFUND
        ],
    ];

    public function __call($name, $arguments)
    {
        if (Str::is('can*Handle', $name)) {
            if (is_null($this->order_status)) {
                throw new Exception("order status is null where call method{$name}!");
            }
            $key = Str::of($name)->replaceFirst('can', '')->replaceLast('Handle', '')->lower();
            return in_array($this->order_status, $this->canHandleMap[(string) $key]);
        } elseif (Str::is('is*Status', $name)) {
            if (is_null($this->order_status)) {
                throw new Exception("order status is null where call method{$name}!");
            }
            $key    = Str::of($name)->replaceFirst('is', '')->replaceLast('Status',
                '')->snake()->upper()->prepend('STATUS');
            $status = (new \ReflectionClass(OrderEnums::class))->getConstant($key);
            return $this->order_status == $status;
        }

        return parent::__call($name, $arguments);
    }


    public function getCanHandleOptions()
    {
        return [
            'cancel'    => $this->canCancelHandle(),
            'delete'    => $this->canDeleteHandle(),
            'pay'       => $this->canPayHandle(),
            'comment'   => $this->canCommentHandle(),
            'confirm'   => $this->canConfirmHandle(),
            'refund'    => $this->canRefundHandle(),
            'aftersale' => $this->canAftersaleHandle(),
            'rebuy'     => $this->canRebuyHandle()
        ];
    }

}
