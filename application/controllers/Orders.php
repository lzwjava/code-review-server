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
}