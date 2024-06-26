<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午8:32
 */
class Rewards extends BaseController
{
    public $orderDao;
    public $rewardDao;
    public $notify;
    public $attendanceDao;
    public $enrollmentDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(OrderDao::class);
        $this->orderDao = new OrderDao();
        $this->load->model(RewardDao::class);
        $this->rewardDao = new RewardDao();
        $this->load->library(Notify::class);
        $this->notify = new Notify();
        $this->load->model(AttendanceDao::class);
        $this->attendanceDao = new AttendanceDao();
        $this->load->model(EnrollmentDao::class);
        $this->enrollmentDao = new EnrollmentDao();
    }

    public function callback_post()
    {

        $content = file_get_contents("php://input");
        logInfo("content $content");
        $event = json_decode($content);
        logInfo("after json encode event " . json_encode($event));
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
            return;
        }
        $object = $event->data->object;
        $orderNo = $object->order_no;
        $charge = $this->chargeDao->getOneByOrderNo($orderNo);
        if ($charge == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "charge with that orderNo not exists");
            return;
        }
        $metadata = $object->metadata;
        if (isset($metadata->orderId)) {
            $orderId = $metadata->orderId;
            $order = $this->orderDao->getOrder($orderId);
            if ($order == null) {
                $this->failure(ERROR_OBJECT_NOT_EXIST, "order with that orderId not exists");
                return;
            }
            $this->chargeDao->updateChargeToPaid($orderNo);
            $rewardId = $this->rewardDao->addReward($order->orderId, $charge->creator, $charge->chargeId);
            $amount = $object->amount;

            if ($order->status == ORDER_STATUS_NOT_PAID) {
                if ($amount < LEAST_FIRST_REWARD) {
                    $info = 'status is not paid but amount less than 5000';
                    logInfo($info);
                    $this->failure(ERROR_PARAMETER_ILLEGAL, $info);
                } else {
                    $this->orderDao->updateOrderToPaid($order->orderId, $rewardId);
                    $this->notify->notifyNewOrder($order);
                    $this->succeed();
                }
            } else {
                $this->succeed();
            }
        } else if (isset($metadata->eventId)) {
            $eventId = $metadata->eventId;
            $userId = $metadata->userId;
            $this->db->trans_start();
            $this->chargeDao->updateChargeToPaid($orderNo);
            $charge = $this->chargeDao->getOneByOrderNo($orderNo);
            $this->attendanceDao->addAttendance($userId, $eventId, $charge->chargeId);
            $this->db->trans_complete();
            $this->notify->notifyAttended($userId, $eventId);
            if ($this->checkIfSQLResWrong($this->db->trans_status())) {
                return;
            }
            $this->succeed();
        } else if (isset($metadata->workshopId)) {
            $workshopId = $metadata->workshopId;
            $userId = $metadata->userId;
            $this->db->trans_start();
            $this->chargeDao->updateChargeToPaid($orderNo);
            $charge = $this->chargeDao->getOneByOrderNo($orderNo);
            $this->enrollmentDao->addEnrollment($userId, $workshopId, $charge->chargeId);
            $this->db->trans_complete();
//            $this->notify->notifyAttended($userId, $eventId);
            if ($this->checkIfSQLResWrong($this->db->trans_status())) {
                return;
            }
            $this->succeed();
        } else {
            $this->failure(ERROR_PARAMETER_ILLEGAL, "not set orderId or eventId in metadata");
        }
    }

    public function refund($orderId)
    {
        $user = $this->getSessionUser();
        if ($user != null) {
            return;
        }
        $order = $this->orderDao->getOrder($orderId);
        if ($user->id != $order->reviewerId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, '仅该订单指定的大神能够退款');
            return;
        }
        // $ch = \Pingpp\Charge::retrieve()
    }

    public function success_get()
    {
        $params = $this->get();
        $paramsStr = json_encode($params);
        logInfo("reward success $paramsStr");
        $totalFee = $this->get("total_fee");
        $eventFee = 288;
        $workshopFee = 20000;
        if ($totalFee != null) {
            $feeNum = intval($this->castToNumber($totalFee));
            if ($feeNum == $eventFee) {
                header("Location: http://reviewcode.cn/paid.html?type=event");
            } else if ($feeNum == $workshopFee || $feeNum == 5000) {
                header("Location: http://reviewcode.cn/paid.html?type=workshop");
            } else {
                header("Location: http://reviewcode.cn/paid.html?type=order");
            }
            return;
        }
        header("Location: http://reviewcode.cn/paid.html");
    }
}
