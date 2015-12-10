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

        $event = json_decode(file_get_contents("php://input"));
        if (!isset($event->type)) {
            $this->failure(ERROR_MISS_PARAMETERS, "please input string");
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
        $this->succeed();
    }

    private function handleChargeSucceed($event)
    {
        if (!isset($event->data) || !isset($event->data->object) ||
            !isset($event->data->object->order_no)
        ) {
            log_message('error', 'event.data.object.order_no is not set.');
        } else {
            $orderNo = $event->data->object->order_no;
            $this->rewardDao->updateRewardToPaid($orderNo);
        }
    }
}
