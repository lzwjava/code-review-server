<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午2:22
 */
class Reviews extends BaseController
{
    function __construct()
    {
        parent::__construct();
        \Pingpp\Pingpp::setApiKey('sk_test_9Giz1SPG8mD4OW94OSTmPGyL');
    }

    public function add()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_ORDER_ID, KEY_CONTENT))) {
            return;
        }
        $orderId = $_POST[KEY_ORDER_ID];
        $content = $_POST[KEY_CONTENT];
        $order = $this->orderDao->getOne($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "找不到相应的 review 订单");
            return;
        }
        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $user = $this->getSessionUser();
        if ($user->id != $order->reviewerId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, "当前登录的用户不是该 review 指定的大神");
            return;
        }
        $insertId = $this->reviewDao->add($orderId, $content);
        $this->succeed($this->reviewDao->getOne($insertId));
    }

    public function edit()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_REVIEW_ID, KEY_CONTENT))) {
            return;
        }
        $reviewId = $_POST[KEY_REVIEW_ID];
        $content = $_POST[KEY_CONTENT];

        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $user = $this->getSessionUser();
        error_log("user $user->id");
        $review = $this->reviewDao->getOne($reviewId);
        if ($review == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "找不到相应的 Review");
            return;
        }

        $orderId = $review->orderId;
        $order = $this->orderDao->getOne($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "找不到相应的 review 订单");
            return;
        }

        if ($user->id != $order->reviewerId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, "当前编辑的用户不是该 Review 指定的大神");
            return;
        }

        $this->reviewDao->updateContent($reviewId, $content);
        $this->succeed($this->reviewDao->getOne($reviewId));
    }

    private function getOrderNo()
    {
        return getToken(16);
    }

    public function reward()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_REVIEW_ID, KEY_AMOUNT))) {
            return;
        }
        $reviewId = $_POST[KEY_REVIEW_ID];
        $amount = $this->castToNumber($_POST[KEY_AMOUNT]);
        if (is_int($amount) == false) {
            $this->failure(ERROR_AMOUNT_UNIT, 'amount 必须为整数, 单位为分钱. 例如 10 元, amount = 1000.');
            return;
        }
        if ($amount < 10) {
            $this->failure(ERROR_AMOUNT_UNIT, '打赏金额最少为 10 分钱');
            return;
        }
        if ($this->checkIfNotInSessionAndResponse()) {
            return;
        }
        $user = $this->getSessionUser();

        $order = $this->orderDao->getOneByReviewId($reviewId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, '没有找到相应的 review 订单');
            return;
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
        $this->rewardDao->add($reviewId, $orderNo, $amount, $user->id, $ipAddress);
        $this->output->set_status_header(200);
        $this->output->set_content_type('application/json', 'utf-8');
        echo($ch);
        // $this->succeed($ch);
    }
}
