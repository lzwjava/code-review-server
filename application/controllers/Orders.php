<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 下午6:54
 */
class Orders extends BaseController
{

    function myOrders_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $status = null;
        if (isset($_GET[KEY_STATUS])) {
            $status = $_GET[KEY_STATUS];
        }

        $skip = $this->getSkip();
        $limit = $this->getLimit();
        if ($user->type == TYPE_LEARNER) {
            $orders = $this->orderDao->getOrdersOfLearner($user->id, $status, $skip, $limit);
        } else {
            $orders = $this->orderDao->getOrdersOfReviewer($user->id, $status, $skip, $limit);
        }
        $this->succeed($orders);
    }

    function add_post()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_GITHUB_URL, KEY_REMARK,
            KEY_REVIEWER_ID, KEY_CODE_LINES, KEY_AMOUNT))
        ) {
            return;
        }
        $gitHubUrl = $_POST[KEY_GITHUB_URL];
        $remark = $_POST[KEY_REMARK];
        $reviewerId = $_POST[KEY_REVIEWER_ID];
        $codeLines = $_POST[KEY_CODE_LINES];
        $amount = $this->castToNumber($_POST[KEY_AMOUNT]);

        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($user->type != TYPE_LEARNER) {
            $this->failure(ERROR_ONLY_LEARNER_CAN_ORDER, "仅是新手才能提交 Review 请求");
            return;
        }
        $reviewer = $this->reviewerDao->getOne($reviewerId);
        if ($reviewer == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "无法找到相应的大神");
            return;
        }
        if ($this->orderDao->hasSameOrder($reviewerId, $user->id, $gitHubUrl)) {
            $this->failure(ERROR_ALREADY_DO_IT, "已经有相同 GitHub 地址的 Review 请求");
            return;
        }
        if ($reviewer->busy) {
            $this->failure(ERROR_EXCEED_MAX_ORDERS, "该大神 Review 申请已满,请稍后再申请");
            return;
        }
        if ($codeLines <= 0) {
            $this->failure(ERROR_CODE_LINES_INVALID, "codeLines 必须大于 0");
            return;
        }
        if ($this->checkIfAmountWrong($amount)) {
            return;
        }
        if ($amount < LEAST_FIRST_REWARD) {
            $yuan = LEAST_FIRST_REWARD / 100;
            $this->failure(ERROR_AMOUNT_UNIT, "申请者打赏金额至少为 $yuan 元");
            return;
        }
        $insertId = $this->orderDao->addOrder($gitHubUrl, $remark, $reviewerId, $user->id, $codeLines, $amount);
        $order = $this->orderDao->getOrder($insertId);
        $this->succeed($order);
    }

    function view_get($orderId)
    {
        $order = $this->orderDao->getOrder($orderId);
        if ($this->checkIfObjectNotExists($order)) {
            return;
        }
        $this->succeed($order);
    }

    public function reward_post($orderId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $order = $this->orderDao->getOrder($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, '没有找到相应的 review 订单');
            return;
        }
        $firstReward = false;
        if ($user->id == $order->learnerId && $order->status == ORDER_STATUS_NOT_PAID) {
            $amount = $order->amount;
            $firstReward = true;
        } else {
            if ($this->checkIfParamsNotExist($_POST, array(KEY_AMOUNT))) {
                return;
            }
            $amount = $this->castToNumber($_POST[KEY_AMOUNT]);
        }
        if ($this->checkIfAmountWrong($amount)) {
            return;
        }
        $reviewerName = $order->reviewer->username;
        $currentUsername = $user->username;
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
        if (isLocalDebug()) {
            // CodeReviewTest
            // $appId = 'app_erTGG4vrzrP008ij';
            $appId = 'app_jTSKu5CmXbHC0q5q';
        } else {
            // CodeReviewProd
            // $appId = 'app_XzDynH4qX5u510mz';
            $appId = 'app_jTSKu5CmXbHC0q5q';
        }
        $ch = \Pingpp\Charge::create(
            array(
                'order_no' => $orderNo,
                'app' => array('id' => $appId),
                'channel' => 'alipay_pc_direct',
                'amount' => $amount,
                'client_ip' => $ipAddress,
                'currency' => 'cny',
                'subject' => "$currentUsername 打赏给 $reviewerName 大神",
                'body' => "打赏给 $reviewerName 大神",
                'metadata' => array(KEY_ORDER_ID => $order->orderId),
                'extra' => array('success_url' => 'http://api.reviewcode.cn/rewards/success')
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

        $chargeId = $this->chargeDao->add($orderNo, $amount, $user->id, $ipAddress);
        //$this->rewardDao->add($order->orderId, $user->id, $chargeId);

        $this->output->set_status_header(200);
        $this->output->set_content_type('application/json', 'utf-8');
        echo($ch);
        // $this->succeed($ch);
    }

    private function getOrderNo()
    {
        return getToken(16);
    }

    public function update_post($orderId)
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_STATUS))) {
            return;
        }
        $status = $_POST[KEY_STATUS];
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($this->checkIfNotInArray($status, $this->allOrderStatus())) {
            return;
        }

        $order = $this->orderDao->getOrder($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, "无法找到当前的订单");
            return;
        }
        if ($order->reviewer->id != $user->id) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, "只有该订单指定的大神才能执行此操作");
            return;
        }
        if ($order->status != ORDER_STATUS_PAID) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, "订单不是已打赏状态, 不能执行操作");
            return;
        }
        $this->orderDao->update($orderId, array(
            KEY_STATUS => $status
        ));
        $this->succeed();
    }

}
