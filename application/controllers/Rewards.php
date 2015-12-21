<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午8:32
 */
class Rewards extends BaseController
{
    public function callback()
    {

        $content = file_get_contents("php://input");
        logInfo("content $content");
        $event = json_decode($content);
        if (!isset($event->type)) {
            $this->failure(ERROR_MISS_PARAMETERS, "please input event type");
            return;
        }
        switch ($event->type) {
            case 'charge.succeeded':
                // 开发者在此处加入对支付异步通知的处理代码
                $this->handleChargeSucceed($event);
                break;
            case "refund.succeeded":
                // 开发者在此处加入对退款异步通知的处理代码
                header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                break;
            default:
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                break;
        }
    }

    private function handleChargeSucceed($event)
    {
        if (!isset($event->data) || !isset($event->data->object) ||
            !isset($event->data->object->order_no)
        ) {
            $this->failure(ERROR_PARAMETER_ILLEGAL, "there are no orderNo in event");
        } else {
            $orderNo = $event->data->object->order_no;
            $charge = $this->chargeDao->getOneByOrderNo($orderNo);
            if ($charge == null) {
                $this->failure(ERROR_OBJECT_NOT_EXIST, "charge with that orderNo not exists");
            } else {
                $this->chargeDao->updateChargeToPaid($orderNo);
                $this->succeed();
            }
        }
    }
}
