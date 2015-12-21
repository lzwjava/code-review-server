<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 下午6:54
 */
class Orders extends BaseController
{
    function index()
    {
        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $user = $this->getSessionUser();
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        error_log("type: $user->type");
        if ($user->type == TYPE_LEARNER) {
            $orders = $this->orderDao->getOrdersOfLearner($user->id, $skip, $limit);
        } else {
            $orders = $this->orderDao->getOrdersOfReviewer($user->id, $skip, $limit);
        }
        $this->succeed($orders);
    }

    function add()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_GITHUB_URL, KEY_REMARK, KEY_REVIEWER_ID))) {
            return;
        }
        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $user = $this->getSessionUser();
        if ($user->type != TYPE_LEARNER) {
            $this->failure(ERROR_ONLY_LEARNER_CAN_ORDER, "仅是新手才能提交 Review 请求");
            return;
        }
        $gitHubUrl = $_POST[KEY_GITHUB_URL];
        $remark = $_POST[KEY_REMARK];
        $reviewerId = $_POST[KEY_REVIEWER_ID];
        error_log($reviewerId);
        $reviewer = $this->reviewerDao->getOne($reviewerId);
        if ($reviewer == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "无法找到相应的大神");
            return;
        }
        $insertId = $this->orderDao->add($gitHubUrl, $remark, $reviewerId, $user->id);
        $order = $this->orderDao->getOne($insertId);
        $this->succeed($order);
    }

    function edit()
    {

    }

    function view()
    {
        if ($this->checkIfParamsNotExist($_GET, array(KEY_ORDER_ID))) {
            return;
        }
        $orderId = $_GET[KEY_ORDER_ID];
        $order = $this->orderDao->getOne($orderId);
        if ($this->checkIfObjectNotExists($order)) {
            return;
        }
        $this->succeed($order);
    }

    public function reward()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_ORDER_ID, KEY_AMOUNT))) {
            return;
        }
        $orderId = $_POST[KEY_ORDER_ID];
        $amount = $this->castToNumber($_POST[KEY_AMOUNT]);
        if (is_int($amount) == false) {
            $this->failure(ERROR_AMOUNT_UNIT, 'amount 必须为整数, 单位为分钱. 例如 10 元, amount = 1000.');
            return;
        }
        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $user = $this->getSessionUser();

        $order = $this->orderDao->getOne($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, '没有找到相应的 review 订单');
            return;
        }
        $firstReward = false;
        if ($user->id == $order->learnerId && $order->status == ORDER_STATUS_NOT_PAID) {
            if ($amount < 5000) {
                $this->failure(ERROR_AMOUNT_UNIT, '申请者打赏金额至少为 5 元');
                return;
            }
            $firstReward = true;
        } else {
            if ($amount < 1000) {
                $this->failure(ERROR_AMOUNT_UNIT, '打赏金额至少为 1 元');
                return;
            }
        }
        $reviewerName = $order->reviewer->username;
        $orderNo = $this->getOrderNo();
        $ipAddress = $this->input->ip_address();
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $this->failure(ERROR_INVALID_IP, '无效的请求源');
            return;
        }
        if ($ipAddress == '::1') {
            // local debug case
            $ipAddress = '127.0.0.1';
        }
        $ch = \Pingpp\Charge::create(
            array(
                'order_no' => $orderNo,
                'app' => array('id' => 'app_erTGG4vrzrP008ij'),
                'channel' => 'alipay_qr',
                'amount' => $amount,
                'client_ip' => $ipAddress,
                'currency' => 'cny',
                'subject' => '打赏',
                'body' => "打赏给 $reviewerName 大神"
            )
        );
        if ($ch == null || $ch->failure_code != null) {
            error_log("charge create failed\n");
            if ($ch != null) {
                error_log("reason $ch->failure_message");
            }
            $this->failure(ERROR_PINGPP_CHARGE, "创建打赏失败");
            return;
        }

        $this->db->trans_start();
        $chargeId = $this->chargeDao->add($orderNo, $amount, $user->id, $ipAddress);
        $this->rewardDao->add($order->orderId, $user->id, $chargeId);
        $this->db->trans_complete();

        $this->output->set_status_header(200);
        $this->output->set_content_type('application/json', 'utf-8');
        echo($ch);
        // $this->succeed($ch);
    }

    private function getOrderNo()
    {
        return getToken(16);
    }

}