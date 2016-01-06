<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 下午6:54
 */
class Orders extends BaseController
{

    function myOrders()
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

    function add()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_GITHUB_URL, KEY_REMARK,
            KEY_REVIEWER_ID, KEY_CODE_LINES))
        ) {
            return;
        }
        $gitHubUrl = $_POST[KEY_GITHUB_URL];
        $remark = $_POST[KEY_REMARK];
        $reviewerId = $_POST[KEY_REVIEWER_ID];
        $codeLines = $_POST[KEY_CODE_LINES];

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
        $insertId = $this->orderDao->add($gitHubUrl, $remark, $reviewerId, $user->id, $codeLines);
        $order = $this->orderDao->getOne($insertId);
        $this->succeed($order);
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
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $order = $this->orderDao->getOne($orderId);
        if ($order == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, '没有找到相应的 review 订单');
            return;
        }
        $firstReward = false;
        if ($user->id == $order->learnerId && $order->status == ORDER_STATUS_NOT_PAID) {
            if ($amount < LEAST_FIRST_REWARD) {
                $this->failure(ERROR_AMOUNT_UNIT, '申请者打赏金额至少为 5 元(amount=500)');
                return;
            }
            $firstReward = true;
        } else {
            if ($amount < LEAST_COMMON_REWARD) {
                $this->failure(ERROR_AMOUNT_UNIT, '打赏金额至少为 1 元(amount=100)');
                return;
            }
            if ($amount > MAX_COMMON_REWARD) {
                $this->failure(ERROR_AMOUNT_UNIT, '打赏金额最多为 1000 元');
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
                'body' => "打赏给 $reviewerName 大神",
                'metadata' => array(KEY_ORDER_ID => $order->orderId)
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

    public function tag()
    {
        if ($this->checkIfParamsNotExist($_POST, array(KEY_OP, KEY_TAG_ID, KEY_ORDER_ID))) {
            return;
        }
        $op = $_POST[KEY_OP];
        $tagId = $_POST[KEY_TAG_ID];
        $orderId = $_POST[KEY_ORDER_ID];
        if ($op != KEY_OP_ADD && $op != KEY_OP_REMOVE) {
            $this->failure(ERROR_PARAMETER_ILLEGAL, "无效的操作");
        } else {
            if ($op == KEY_OP_ADD) {
                $this->tagDao->addOrderTag($orderId, $tagId);
            } else {
                $this->tagDao->removeOrderTag($orderId, $tagId);
            }
            $this->succeed($this->tagDao->getOrderTags($orderId));
        }
    }

    public function update($orderId)
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

        $order = $this->orderDao->getOne($orderId);
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

    public function userOrders($userId)
    {
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        $this->succeed();
        $user = $this->userDao->findPublicUserById($userId);
        if ($user == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST, '用户不存在');
            return;
        }
        if ($user->type == KEY_LEARNER_ID) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT, '只能请求大神的订单案例');
            return;
        }
        $orders = $this->orderDao->getOrdersOfReviewer($userId, ORDER_STATUS_FINISHED, $skip, $limit);
        $this->succeed($orders);
    }

    public function allOrders()
    {
        $skip = $this->getSkip();
        $limit = $this->getLimit();
        $orders = $this->orderDao->getDisplayingOrders($skip, $limit);
        $this->succeed($orders);
    }
}
