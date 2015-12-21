<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午2:22
 */
class Reviews extends BaseController
{

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

}
